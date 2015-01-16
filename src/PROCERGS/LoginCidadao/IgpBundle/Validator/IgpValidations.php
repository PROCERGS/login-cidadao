<?php

namespace PROCERGS\LoginCidadao\IgpBundle\Validator;

class IgpValidations
{

    const MESSAGE_LENGTH = 'The Id Card number must be 10 characters long.';
    const MESSAGE_INVALID = 'Invalid Id Card number.';
    const MESSAGE_WEBSERVICE_UNAVAILABLE = "It wasn't possible to validate your Id Card at this time. Please try again in a little while.";
    const MESSAGE_VALUE_MISMATCH = 'The value does not match the value of the IGP';
    const MESSAGE_IDCARD_PROBLEM = 'Id Card irregular situation';
    const MESSAGE_IMUTABLE_VALID_IDCARD = "It is not possible to change a Id Card after validated";

    public static function checkIdCardNumber($number)
    {
        @$check1 = IgpValidations::checkRGDce($number) == $number[0];
        @$check2 = IgpValidations::checkRGDcd($number) == $number[9];
        return $check1 && $check2;
    }

    private static function checkRGDce($rg)
    {
        $total = ($rg[1] * 2) + ($rg[2] * 3) + ($rg[3] * 4) + ($rg[4] * 5) + ($rg[5] * 6) + ($rg[6] * 7) + ($rg[7] * 8) + ($rg[8] * 9);
        $resto = $total % 11;

        if ($resto == 0 || $resto == 1) {
            return 1;
        } else {
            return 11 - $resto;
        }
    }

    private static function checkRGDcd($rg)
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
