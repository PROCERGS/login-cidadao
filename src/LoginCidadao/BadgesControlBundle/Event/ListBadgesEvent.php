<?php

namespace LoginCidadao\BadgesControlBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use LoginCidadao\BadgesControlBundle\Model\BadgeEvaluatorInterface;
use LoginCidadao\BadgesControlBundle\Exception\BadgesNameCollisionException;

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
        $namespace = $evaluator->getName();
        if (!array_key_exists($namespace, $this->badges)) {
            $this->badges[$namespace] = $evaluator->getAvailableBadges();
        } else {
            foreach ($evaluator->getAvailableBadges() as $name => $badge) {
                if (array_key_exists($name, $this->badges[$namespace])) {
                    throw new BadgesNameCollisionException("There is another badge named '{$namespace}.{$name}'.");
                }
                $this->badges[$namespace][$name] = $badge;
            }
        }
        return $this;
    }
    
    public function getBadges()
    {
        return $this->badges;
    }

}
