<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;

class ProfileFormType extends BaseType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('firstName');
        $builder->add('surname');
        $builder->add('birthdate', 'birthday');
        $builder->add('cep');
        $builder->add('cpf');
    }

    public function getName()
    {
        return 'procergs_person_profile';
    }

}