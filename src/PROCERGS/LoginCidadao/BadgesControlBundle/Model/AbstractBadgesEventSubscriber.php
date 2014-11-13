<?php

namespace PROCERGS\LoginCidadao\BadgesControlBundle\Model;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use PROCERGS\LoginCidadao\BadgesControlBundle\BadgesEvents;
use PROCERGS\LoginCidadao\BadgesControlBundle\Event\ListBadgesEvent;
use PROCERGS\LoginCidadao\BadgesControlBundle\Event\EvaluateBadgesEvent;

abstract class AbstractBadgesEventSubscriber implements EventSubscriberInterface, BadgeEvaluatorInterface
{

    protected $badges = array();
    protected $name;

    abstract public function onBadgeEvaluate(EvaluateBadgesEvent $event);

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
            BadgesEvents::BADGES_LIST_AVAILABLE => array('onBadgeListAvailable', 0)
        );
    }

    public function onBadgeListAvailable(ListBadgesEvent $event)
    {
        $event->registerBadges($this);
    }

    protected function registerBadge($name, $description, $extras = null)
    {
        if (is_null($extras)) {
            $extras = array();
        }
        $this->badges[$name] = array_merge(compact('description'), $extras);
    }

}
