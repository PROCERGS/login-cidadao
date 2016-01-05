<?php

namespace LoginCidadao\ValidationControlBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use LoginCidadao\CoreBundle\Entity\State;
use LoginCidadao\CoreBundle\Model\IdCardInterface;

class InstantiateIdCardEvent extends Event
{

    /** @var State */
    protected $state;

    /** @var IdCardInterface */
    protected $idCard;

    public function __construct(State $state = null)
    {
        $this->state = $state;
    }

    /**
     * @return State
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return IdCardInterface
     */
    public function getIdCard()
    {
        return $this->idCard;
    }

    /**
     * @param IdCardInterface $idCard
     * @return InstantiateIdCardEvent
     */
    public function setIdCard(IdCardInterface $idCard)
    {
        $this->idCard = $idCard;
        return $this;
    }

}
