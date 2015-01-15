<?php
namespace PROCERGS\LoginCidadao\IgpBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use PROCERGS\LoginCidadao\IgpBundle\Entity\IgpWs;

class RGValidator extends ConstraintValidator
{

    protected $igpWs;

    public function __construct(IgpWs $igWs)
    {
        $this->igpWs = $igWs;
    }

    public function validate($value, Constraint $constraint)
    {
        if ($value === null || ! (strlen(trim($value)))) {
            return;
        }
        $rgNum = $this->context->getRoot()
            ->get('value')
            ->getData();
        
        if (strlen($rgNum) != 10) {
            $this->context->addViolationAt('value', $constraint->lengthMessage);
        }
        if ($this->checkRGDce($rgNum) != $rgNum[0] || $this->checkRGDcd($rgNum) != $rgNum[9]) {
            $this->context->addViolationAt('value', $constraint->message);
        }
        $this->igpWs->setRg($rgNum);
        $res = $this->igpWs->consultar();
        if ($res['cod_retorno'] != 1) {
            $this->context->addViolationAt('value', $res['mensagem_retorno']);
        }
    }

    private function checkRGDce(&$rg)
    {
        $total = ($rg[1] * 2) + ($rg[2] * 3) + ($rg[3] * 4) + ($rg[4] * 5) + ($rg[5] * 6) + ($rg[6] * 7) + ($rg[7] * 8) + ($rg[8] * 9);
        $resto = $total % 11;
        
        if ($resto == 0 || $resto == 1) {
            return 1;
        } else {
            return 11 - $resto;
        }
    }

    private function checkRGDcd(&$rg)
    {
        $n1 = ($rg[8] * 2) % 9;
        $n2 = ($rg[6] * 2) % 9;
        $n3 = ($rg[4] * 2) % 9;
        $n4 = ($rg[2] * 2) % 9;
        $n5 = ($rg[0] * 2) % 9;
        $total = $n1 + $n2 + $n3 + $n4 + $n5 + $rg[7] + $rg[5] + $rg[3] + $rg[1];
        if ($rg[8] == 9) {
            $total = $total + 9;
        }
        if ($rg[6] == 9) {
            $total = $total + 9;
        }
        if ($rg[4] == 9) {
            $total = $total + 9;
        }
        if ($rg[2] == 9) {
            $total = $total + 9;
        }
        if ($rg[0] == 9) {
            $total = $total + 9;
        }
        $resto = $total % 10;
        if ($resto == 0) {
            return 1;
        } else {
            return 10 - $resto;
        }
    }
}
