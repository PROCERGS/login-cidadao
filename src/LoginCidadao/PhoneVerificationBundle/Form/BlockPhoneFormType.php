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
use LoginCidadao\PhoneVerificationBundle\Model\BlockPhoneNumberRequest;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlockPhoneFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('phoneNumber', PhoneNumberType::class, [
                'required' => true,
                'label' => 'admin.blocklist.new.form.phone.label',
                'attr' => [
                    'class' => 'form-control intl-tel',
                    'placeholder' => 'admin.blocklist.new.form.phone.placeholder',
                ],
                'label_attr' => ['class' => 'intl-tel-label'],
                'format' => PhoneNumberFormat::E164,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'admin.blocklist.new.form.submit.label',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BlockPhoneNumberRequest::class,
        ]);
    }
}
