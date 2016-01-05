<?php

namespace LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\ResettingFormType as BaseType;

class ResettingFormType extends BaseType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('plainPassword', 'repeated', array(
            'type' => 'password',
            'attr' => array('autocomplete' => 'off'),
            'options' => array('translation_domain' => 'FOSUserBundle'),
            'first_options' => array('label' => 'form.new_password'),
            'second_options' => array('label' => 'form.new_password_confirmation'),
            'invalid_message' => 'fos_user.password.mismatch',
        ))
        ->add('save', 'submit');
    }

    public function getName()
    {
        return 'lc_person_resetting';
    }
}
