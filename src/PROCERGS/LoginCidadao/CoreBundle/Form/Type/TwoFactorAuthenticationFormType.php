<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Translation\TranslatorInterface;

class TwoFactorAuthenticationFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('googleAuthenticatorSecret', 'text',
                  array(
                'read_only' => true,
                'label' => "Authenticator Secret"
            ))
            ->add('verification', 'text',
                array(
                'label' => 'Generated Code',
                'mapped' => false
        ));
        if (strlen($builder->getData()->getPassword()) == 0) {
            $builder->add('plainPassword', 'repeated',
                array(
                'type' => 'password'
            ));
        } else {
            $builder->add('current_password', 'password',
                array(
                'required' => true,
                'constraints' => new UserPassword(),
                'mapped' => false
            ));
        }
        $builder
            ->add('enable', 'submit',
                  array(
                'attr' => array('class' => 'btn btn-success'),
                'label' => 'Activate Two-Factor Authentication')
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PROCERGS\LoginCidadao\CoreBundle\Entity\Person'
        ));
    }

    public function getName()
    {
        return 'lc_2fa';
    }

}
