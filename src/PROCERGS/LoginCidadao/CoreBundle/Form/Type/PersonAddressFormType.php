<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

class PersonAddressFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null,
                  array('attr' => array('class' => 'form-control')))
            ->add('line1', null,
                  array('attr' => array('class' => 'form-control'), 'label' => 'Address'))
            ->add('line2', null,
                  array('attr' => array('class' => 'form-control'), 'label' => 'Address Second Line', 'required' => false))
            ->add('city', null,
                  array('attr' => array('class' => 'form-control')))
            ->add('postalCode', null,
                  array('attr' => array('class' => 'form-control')))
            ->add('save', 'submit',
                  array('attr' => array('class' => 'btn btn-success')));
    }

    public function getName()
    {
        return 'lc_person_address';
    }

}
