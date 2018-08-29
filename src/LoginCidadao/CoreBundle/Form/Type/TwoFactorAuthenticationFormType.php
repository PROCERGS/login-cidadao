<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Form\Type;

use LoginCidadao\CoreBundle\Entity\Person;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class TwoFactorAuthenticationFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('googleAuthenticatorSecret', TextType::class, [
                'attr' => ['readonly' => true],
                'label' => "Authenticator Secret",
            ])
            ->add('verification', TextType::class, [
                'label' => 'Generated Code',
                'mapped' => false,
            ]);
        if (strlen($builder->getData()->getPassword()) == 0) {
            $builder->add('plainPassword', RepeatedType::class, [
                'type' => 'password',
                'attr' => ['autocomplete' => 'off'],
            ]);
        } else {
            $builder->add('current_password', PasswordType::class, [
                'required' => true,
                'attr' => ['autocomplete' => 'off'],
                'constraints' => new UserPassword(),
                'mapped' => false,
            ]);
        }
        $builder
            ->add('enable', SubmitType::class, [
                'attr' => ['class' => 'btn btn-success'],
                'label' => 'Activate Two-Factor Authentication',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Person::class,
        ]);
    }

    public function getName()
    {
        return 'lc_2fa';
    }
}
