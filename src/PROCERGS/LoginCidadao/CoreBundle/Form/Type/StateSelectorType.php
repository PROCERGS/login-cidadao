<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Form\Type;

use PROCERGS\LoginCidadao\CoreBundle\Form\DataTransformer\StateToStringTransformer;

class StateSelectorType extends AbstractTextSelectorType
{

    public function getName()
    {
        return 'state_selector';
    }

    public function getTransformer()
    {
        return new StateToStringTransformer($this->em);
    }

}
