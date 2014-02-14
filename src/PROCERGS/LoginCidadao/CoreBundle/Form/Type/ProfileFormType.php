<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;

class ProfileFormType extends BaseType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('username', null, array('read_only'=>true, 'label' => 'form.username', 'translation_domain' => 'FOSUserBundle'));
        $builder->add('email', 'email', array('label' => 'form.email', 'translation_domain' => 'FOSUserBundle'));
        $builder->add('firstName', 'text', array('label' => 'form.firstName', 'translation_domain' => 'FOSUserBundle'));
        $builder->add('surname', 'text', array('label' => 'form.surname', 'translation_domain' => 'FOSUserBundle'));
        $builder->add('cep', null, array('required' => false, 'label' => 'form.cep', 'translation_domain' => 'FOSUserBundle'));
        $builder->add('cpf', null, array('required' => false, 'label' => 'form.cpf', 'translation_domain' => 'FOSUserBundle') );
        $builder->add('birthdate', 'birthday', array(
            'required' => false,
            'format' => 'dd MMMM yyyy',
            'widget' => 'choice',
            'years' => range(date('Y'), date('Y')-70),
            'label' => 'form.birthdate', 
            'translation_domain' => 'FOSUserBundle')
        );
    }

    public function getName()
    {
        return 'procergs_person_profile';
    }

}