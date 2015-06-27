<?php

namespace PROCERGS\LoginCidadao\BadgesControlBundle\Handler;

use PROCERGS\LoginCidadao\BadgesControlBundle\Model\BadgeEvaluatorInterface;
use PROCERGS\LoginCidadao\CoreBundle\Model\PersonInterface;
use PROCERGS\LoginCidadao\BadgesControlBundle\Model\BadgeInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use PROCERGS\LoginCidadao\BadgesControlBundle\BadgesEvents;
use PROCERGS\LoginCidadao\BadgesControlBundle\Event\ListBadgesEvent;
use PROCERGS\LoginCidadao\BadgesControlBundle\Event\ListBearersEvent;
use PROCERGS\LoginCidadao\BadgesControlBundle\Event\EvaluateBadgesEvent;

class BadgesHandler
{

    protected $evaluators;
    protected $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->evaluators = array();
        $this->dispatcher = $dispatcher;

        $this->setup();
    }

    public function register(BadgeEvaluatorInterface $evaluator)
    {
        $id = $evaluator->getId();
        if (!array_key_exists($id, $this->evaluators)) {
            $this->evaluators[$evaluator->getId()] = $evaluator;
        }
        return $this;
    }

    /**
     * Evaluates the badges for a given person.
     *
     * @param PersonInterface $person
     * @return PersonInterface instance with badges
     */
    public function evaluate(PersonInterface $person)
    {
        $event = new EvaluateBadgesEvent($person);
        $this->dispatcher->dispatch(BadgesEvents::BADGES_EVALUATE, $event);
        return $event->getPerson();
    }

    protected function setup()
    {

    }

    public function getAvailableBadges()
    {
        $event = new ListBadgesEvent();
        $this->dispatcher->dispatch(BadgesEvents::BADGES_LIST_AVAILABLE, $event);
        return $event->getBadges();
    }

    public function countBearers(BadgeInterface $badge = null, $value = null)
    {
        $event = new ListBearersEvent($badge, $value);
        $this->dispatcher->dispatch(BadgesEvents::BADGES_LIST_BEARERS, $event);
        return $event->getCount();
    }

}
