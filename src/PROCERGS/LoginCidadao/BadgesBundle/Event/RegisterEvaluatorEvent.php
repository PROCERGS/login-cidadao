<?php

namespace PROCERGS\LoginCidadao\BadgesBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use PROCERGS\LoginCidadao\BadgesBundle\Model\BadgeEvaluatorInterface;

class RegisterEvaluatorEvent extends Event
{

    /** @var BadgeEvaluatorInterface **/
    protected $evaluator;

    public function __construct(BadgeEvaluatorInterface $evaluator)
    {
        $this->evaluator = $evaluator;
    }

    /**
     * @return BadgeEvaluatorInterface
     */
    public function getEvaluator()
    {
        return $this->evaluator;
    }

}
