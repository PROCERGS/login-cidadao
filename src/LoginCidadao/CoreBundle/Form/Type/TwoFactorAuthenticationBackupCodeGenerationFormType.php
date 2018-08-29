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
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class TwoFactorAuthenticationBackupCodeGenerationFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('current_password', PasswordType::class, [
                'label' => 'To prevent unauthorized Backup Code generation type your password to confirm:',
                'attr' => ['autocomplete' => 'off'],
                'required' => true,
                'constraints' => new UserPassword(),
                'mapped' => false,
            ])
            ->add('generate', SubmitType::class, [
                'attr' => ['class' => 'btn btn-success'],
                'label' => 'Generate new Backup Codes',
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
        return 'lc_generate_2fa_backup_codes';
    }
}
