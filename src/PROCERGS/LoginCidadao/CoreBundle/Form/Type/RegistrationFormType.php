<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;

class RegistrationFormType extends BaseType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('firstName', 'text', array('label' => 'form.firstName', 'translation_domain' => 'FOSUserBundle'));
        $builder->add('surname', 'text', array('label' => 'form.surname', 'translation_domain' => 'FOSUserBundle'));
        $builder->add('birthdate', 'birthday', array('label' => 'form.birthdate', 'translation_domain' => 'FOSUserBundle'));
        $builder->add('cep', null, array('label' => 'form.cep', 'translation_domain' => 'FOSUserBundle'));
        $builder->add('cpf', null, array('label' => 'form.cpf', 'translation_domain' => 'FOSUserBundle') );
    }

    public function getName()
    {
        return 'procergs_person_registration';
    }

}
