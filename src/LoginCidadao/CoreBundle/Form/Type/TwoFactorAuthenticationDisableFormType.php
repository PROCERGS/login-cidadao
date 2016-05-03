<?php

namespace LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class TwoFactorAuthenticationDisableFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('current_password', 'password',
                array(
                'label' => 'If you want to proceed, type your account\'s password to confirm:',
                'attr' => array('autocomplete' => 'off'),
                'required' => true,
                'constraints' => new UserPassword(),
                'mapped' => false
            ))
            ->add('disable', 'submit',
                array(
                'attr' => array('class' => 'btn btn-danger'),
                'label' => 'I understand the risks. Disable Two-Factor Authentication')
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'LoginCidadao\CoreBundle\Entity\Person'
        ));
    }

    public function getName()
    {
        return 'lc_disable_2fa';
    }
}
