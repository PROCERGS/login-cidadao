<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\ChangePasswordFormType as BaseType;
use Symfony\Component\Security\Core\SecurityContextInterface;

class ChangePasswordFormType extends BaseType
{

    private $context;

    public function __construct($class, SecurityContextInterface $context)
    {
        parent::__construct($class);
        $this->context = $context;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $this->context->getToken()->getUser();

        $emptyPassword = strlen($user->getPassword()) == 0;
        if (!$emptyPassword) {
            parent::buildForm($builder, $options);
        } else {
            $builder->add('plainPassword', 'repeated',
                    array(
                'type' => 'password',
                'options' => array('translation_domain' => 'FOSUserBundle'),
                'first_options' => array('label' => 'form.new_password'),
                'second_options' => array('label' => 'form.new_password_confirmation'),
                'invalid_message' => 'fos_user.password.mismatch',
            ));
        }
    }

    public function getName()
    {
        return 'procergs_change_password';
    }

}
