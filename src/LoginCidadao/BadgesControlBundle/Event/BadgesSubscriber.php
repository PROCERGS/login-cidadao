<?php

namespace LoginCidadao\BadgesControlBundle\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use LoginCidadao\BadgesControlBundle\BadgesEvents;
use LoginCidadao\BadgesControlBundle\Handler\BadgesHandler;

class BadgesSubscriber implements EventSubscriberInterface
{
    /** @var BadgesHandler */
    protected $handler;

    public function __construct(BadgesHandler $handler)
    {
        $this->handler = $handler;
    }

    public static function getSubscribedEvents()
    {
        return array(
            BadgesEvents::BADGES_REGISTER_EVALUATOR => array('onRegisterEvaluator',
                0),
            BadgesEvents::BADGES_EVALUATE => array('onBadgeEvaluate', 0)
        );
    }

    public function onBadgeEvaluate(EvaluateBadgesEvent $event)
    {
        //
    }

    public function onRegisterEvaluator(RegisterEvaluatorEvent $event)
    {
        $evaluator = $event->getEvaluator();
        $id        = $evaluator->getName();
        $this->handler->register($evaluator);
        error_log("Evaluator $id registered");
    }
}
