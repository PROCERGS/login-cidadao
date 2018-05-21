<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\BadgesControlBundle\Model;

use LoginCidadao\BadgesControlBundle\Exception\BadgesNameCollisionException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use LoginCidadao\BadgesControlBundle\BadgesEvents;
use LoginCidadao\BadgesControlBundle\Event\ListBadgesEvent;
use LoginCidadao\BadgesControlBundle\Event\ListBearersEvent;
use LoginCidadao\BadgesControlBundle\Event\EvaluateBadgesEvent;

abstract class AbstractBadgesEventSubscriber implements EventSubscriberInterface, BadgeEvaluatorInterface
{

    protected $badges = [];
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
        return [
            BadgesEvents::BADGES_EVALUATE => ['onBadgeEvaluate', 0],
            BadgesEvents::BADGES_LIST_AVAILABLE => ['onBadgeListAvailable', 0],
            BadgesEvents::BADGES_LIST_BEARERS => ['onListBearersPreFilter', 0],
        ];
    }

    /**
     * @param ListBadgesEvent $event
     * @throws BadgesNameCollisionException
     */
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
        if ($filterBadge instanceof BadgeInterface && $filterBadge->getNamespace() === $this->getName()) {
            $this->onListBearers($event);
        }
    }

    protected function registerBadge($name, $description, $extras = null)
    {
        $this->badges[$name] = array_merge(compact('description'), is_null($extras) ? [] : $extras);
    }

}
