<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CityType extends TextType
{

    public function getParent()
    {
        return 'form';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        return parent::buildForm($builder, $options);
    }

    public function getName()
    {
        return 'city';
    }

}
