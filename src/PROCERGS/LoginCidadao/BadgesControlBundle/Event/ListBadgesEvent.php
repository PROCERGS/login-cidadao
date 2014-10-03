<?php

namespace PROCERGS\LoginCidadao\BadgesControlBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use PROCERGS\LoginCidadao\BadgesControlBundle\Model\BadgeEvaluatorInterface;
use PROCERGS\LoginCidadao\BadgesControlBundle\Exception\BadgesNameCollisionException;

class ListBadgesEvent extends Event
{

    protected $badges;

    public function __construct()
    {
        $this->badges = array();
    }

    /**
     * 
     * @return array
     */
    public function registerBadges(BadgeEvaluatorInterface $evaluator)
    {
        $name = $evaluator->getName();
        if (!array_key_exists($name, $this->badges)) {
            $this->badges[$name] = $evaluator->getAvailableBadges();
        } else {
            throw new BadgesNameCollisionException("There is another bundle using the '$name' namespace.");
        }
        return $this;
    }
    
    public function getBadges()
    {
        return $this->badges;
    }

}
