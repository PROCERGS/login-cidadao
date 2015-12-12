<?php

namespace LoginCidadao\CoreBundle\Form\Type;

use LoginCidadao\CoreBundle\Form\DataTransformer\StateToStringTransformer;

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
