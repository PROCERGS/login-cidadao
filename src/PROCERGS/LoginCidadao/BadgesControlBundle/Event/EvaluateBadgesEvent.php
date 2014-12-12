<?php

namespace PROCERGS\LoginCidadao\BadgesControlBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use PROCERGS\LoginCidadao\CoreBundle\Model\PersonInterface;
use PROCERGS\LoginCidadao\BadgesControlBundle\Model\BadgeInterface;

class EvaluateBadgesEvent extends Event
{

    protected $person;

    public function __construct(PersonInterface $person)
    {
        $this->person = $person;
    }

    /**
     *
     * @return PersonInterface
     */
    public function getPerson()
    {
        return $this->person;
    }

    public function registerBadges(array $badges)
    {
        foreach ($badges as $badge) {
            $this->registerBadge($badge);
        }
        return $this;
    }

    public function registerBadge(BadgeInterface $badge)
    {
        $namespace = $badge->getNamespace();
        $name = $badge->getName();
        $badgeArray = array(
            "$namespace.$name" => $badge->getData()
        );

        $this->getPerson()->mergeBadges($badgeArray);
    }

}
