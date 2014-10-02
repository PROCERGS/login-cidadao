<?php

namespace PROCERGS\LoginCidadao\BadgesBundle\Model;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use PROCERGS\LoginCidadao\BadgesBundle\BadgesEvents;
use PROCERGS\LoginCidadao\BadgesBundle\Event\ListBadgesEvent;
use PROCERGS\LoginCidadao\BadgesBundle\Event\EvaluateBadgesEvent;

abstract class AbstractBadgesEventSubscriber implements EventSubscriberInterface, BadgeEvaluatorInterface
{

    abstract public function onBadgeEvaluate(EvaluateBadgesEvent $event);

    abstract public function getName();

    abstract public function getAvailableBadges();

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

}
