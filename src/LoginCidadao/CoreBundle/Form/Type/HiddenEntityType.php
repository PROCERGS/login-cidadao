<?php

namespace LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;

class HiddenEntityType extends AbstractType
{

    public function getParent()
    {
        return 'entity';
    }

    public function getName()
    {
        return 'hidden_entity';
    }
}
