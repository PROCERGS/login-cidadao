<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
            ->add('city', 'city_selector',
                  array('attr' => array('class' => 'form-control')))
            ->add('postalCode', null,
                  array('attr' => array('class' => 'form-control')))
            ->add('save', 'submit',
                  array('attr' => array('class' => 'btn btn-success')));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PROCERGS\LoginCidadao\CoreBundle\Entity\PersonAddress'
        ));
    }

    public function getName()
    {
        return 'lc_person_address';
    }

}
