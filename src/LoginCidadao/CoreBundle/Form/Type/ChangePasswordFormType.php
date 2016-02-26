<?php

namespace LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\ChangePasswordFormType as BaseType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ChangePasswordFormType extends BaseType
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct($class, TokenStorageInterface $tokenStorage)
    {
        parent::__construct($class);
        $this->tokenStorage = $tokenStorage;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $this->tokenStorage->getToken()->getUser();

        $emptyPassword = strlen($user->getPassword()) == 0;
        if (!$emptyPassword) {
            parent::buildForm($builder, $options);
            $builder->get('plainPassword')->setAttribute('autocomplete', 'off');
            $builder->get('current_password')->setAttribute('autocomplete',
                'off');
        } else {
            $builder->add('plainPassword',
                'Symfony\Component\Form\Extension\Core\Type\RepeatedType',
                array(
                'type' => 'Symfony\Component\Form\Extension\Core\Type\PasswordType',
                'attr' => array('autocomplete' => 'off'),
                'options' => array('translation_domain' => 'FOSUserBundle'),
                'first_options' => array('label' => 'form.new_password'),
                'second_options' => array('label' => 'form.new_password_confirmation'),
                'invalid_message' => 'fos_user.password.mismatch',
            ));
        }
    }
}
