<?php

namespace LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;

class RegistrationFormType extends BaseType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', 'email',
                array(
                'required' => true,
                'label' => 'form.email',
                'translation_domain' => 'FOSUserBundle')
            )
            ->add('plainPassword', 'password',
                array(
                'required' => true,
                'label' => 'form.password',
                'attr' => array('autocomplete' => 'off'),
                'translation_domain' => 'FOSUserBundle')
            )
            ->add('mobile', null,
                array(
                'required' => false,
                'label' => 'form.mobile',
                'translation_domain' => 'FOSUserBundle')
            )
        ;
    }

    public function getName()
    {
        return 'lc_person_registration';
    }
}
