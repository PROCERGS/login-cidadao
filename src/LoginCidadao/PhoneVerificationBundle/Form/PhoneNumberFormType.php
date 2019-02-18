<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\PhoneVerificationBundle\Form;

use libphonenumber\PhoneNumberFormat;
use LoginCidadao\CoreBundle\Entity\Person;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PhoneNumberFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('mobile', PhoneNumberType::class, [
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Person::class,
            'validation_groups' => ['LoginCidadaoEmailForm'],
        ]);
    }
}
