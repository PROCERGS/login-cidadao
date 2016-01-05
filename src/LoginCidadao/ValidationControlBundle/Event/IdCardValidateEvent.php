<?php

namespace LoginCidadao\ValidationControlBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use LoginCidadao\CoreBundle\Entity\State;
use LoginCidadao\CoreBundle\Model\IdCardInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ExecutionContextInterface;

class IdCardValidateEvent extends Event
{

    /** @var ExecutionContextInterface */
    private $validatorContext;

    /** @var Constraint */
    private $constraint;

    /** @var IdCardInterface */
    private $idCard;

    public function __construct(ExecutionContextInterface $validator,
                                Constraint $constraint, IdCardInterface $idCard)
    {
        $this->setValidatorContext($validator);
        $this->setConstraint($constraint);
        $this->setIdCard($idCard);
    }

    /**
     * @return ExecutionContextInterface
     */
    public function getValidatorContext()
    {
        return $this->validatorContext;
    }

    /**
     * @return Constraint
     */
    public function getConstraint()
    {
        return $this->constraint;
    }

    /**
     * @return IdCardInterface
     */
    public function getIdCard()
    {
        return $this->idCard;
    }

    /**
     * @param ExecutionContextInterface $validatorContext
     * @return IdCardValidateEvent
     */
    public function setValidatorContext(ExecutionContextInterface $validatorContext)
    {
        $this->validatorContext = $validatorContext;
        return $this;
    }

    /**
     * @param Constraint $constraint
     * @return IdCardValidateEvent
     */
    public function setConstraint(Constraint $constraint)
    {
        $this->constraint = $constraint;
        return $this;
    }

    /**
     * @param IdCardInterface $idCard
     * @return IdCardValidateEvent
     */
    public function setIdCard(IdCardInterface $idCard)
    {
        $this->idCard = $idCard;
        return $this;
    }

}
