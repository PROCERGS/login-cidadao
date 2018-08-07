<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\AccountRecoveryBundle\Form;

use libphonenumber\PhoneNumberFormat;
use LoginCidadao\AccountRecoveryBundle\Entity\AccountRecoveryData;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccountRecoveryDataType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class)
            ->add('mobile', PhoneNumberType::class,
                [
                    'required' => false,
                    'label' => 'person.form.mobile.label',
                    'attr' => [
                        'class' => 'form-control intl-tel',
                        'placeholder' => 'person.form.mobile.placeholder',
                    ],
                    'label_attr' => ['class' => 'intl-tel-label'],
                    'format' => PhoneNumberFormat::E164,
                ]);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AccountRecoveryData::class,
        ]);
    }


    /**
     * @return string
     */
    public function getName()
    {
        return 'accrecoverydata';
    }
}
