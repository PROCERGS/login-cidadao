<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Exception;

use Symfony\Component\Form\FormError;

class NfgException extends \Exception
{
    const E_AUTH = 0;
    const E_LOGIN = 1;
    const E_BIND = 2;
}
