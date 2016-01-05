<?php

namespace LoginCidadao\CoreBundle\Form\Type;

use LoginCidadao\CoreBundle\Form\DataTransformer\CountryToStringTransformer;

class CountrySelectorType extends AbstractTextSelectorType
{

    public function getName()
    {
        return 'country_selector';
    }

    public function getTransformer()
    {
        return new CountryToStringTransformer($this->em);
    }

}
