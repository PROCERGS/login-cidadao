<?php

namespace LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;

class RegistrationFormType extends BaseType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email',
                'Symfony\Component\Form\Extension\Core\Type\EmailType',
                array(
                'required' => true,
                'label' => 'form.email',
                'attr' => array('placeholder' => 'form.email.example'),
                'translation_domain' => 'FOSUserBundle'
                )
            )
            ->add('plainPassword',
                'Symfony\Component\Form\Extension\Core\Type\RepeatedType',
                array(
                'required' => true,
                'type' => 'Symfony\Component\Form\Extension\Core\Type\PasswordType',
                'attr' => array(
                    'autocomplete' => 'off',
                    'placeholder' => 'form.plainPassword.example'
                ),
                'options' => array('translation_domain' => 'FOSUserBundle'),
                'first_options' => array(
                    'label' => 'form.password',
                    'attr' => array('placeholder' => 'form.plainPassword.example')
                ),
                'second_options' => array(
                    'label' => 'form.password_confirmation',
                    'attr' => array('placeholder' => 'form.plainPassword.confirm.example')
                ),
                'invalid_message' => 'fos_user.password.mismatch'
                )
            )
        ;
    }

    public function getBlockPrefix()
    {
        return 'lc_person_registration';
    }
}
