<?php

namespace LoginCidadao\BadgesControlBundle\Model;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use LoginCidadao\BadgesControlBundle\BadgesEvents;
use LoginCidadao\BadgesControlBundle\Event\ListBadgesEvent;
use LoginCidadao\BadgesControlBundle\Event\ListBearersEvent;
use LoginCidadao\BadgesControlBundle\Event\EvaluateBadgesEvent;

abstract class AbstractBadgesEventSubscriber implements EventSubscriberInterface, BadgeEvaluatorInterface
{

    protected $badges = array();
    protected $name;

    abstract public function onBadgeEvaluate(EvaluateBadgesEvent $event);

    abstract public function onListBearers(ListBearersEvent $event);

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getAvailableBadges()
    {
        return $this->badges;
    }

    public static function getSubscribedEvents()
    {
        return array(
            BadgesEvents::BADGES_EVALUATE => array('onBadgeEvaluate', 0),
            BadgesEvents::BADGES_LIST_AVAILABLE => array('onBadgeListAvailable', 0),
            BadgesEvents::BADGES_LIST_BEARERS => array('onListBearersPreFilter', 0)
        );
    }

    public function onBadgeListAvailable(ListBadgesEvent $event)
    {
        $event->registerBadges($this);
    }

    /**
     * This method performs a check to verify if the filtered badge (if present)
     * belongs to the namespace of this evaluator.
     *
     * @param ListBearersEvent $event
     */
    public function onListBearersPreFilter(ListBearersEvent $event)
    {
        $filterBadge = $event->getBadge();
        if ($filterBadge instanceof BadgeInterface) {
            if ($filterBadge->getNamespace() !== $this->getName()) {
                return;
            }
        }

        $this->onListBearers($event);
    }

    protected function registerBadge($name, $description, $extras = null)
    {
        if (is_null($extras)) {
            $extras = array();
        }
        $this->badges[$name] = array_merge(compact('description'), $extras);
    }

}
