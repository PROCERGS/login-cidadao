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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;

class SearchPhoneNumberType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('phone', TelType::class, [
                'required' => true,
                'label' => 'admin.blocklist.list.form.phone.label',
                'attr' => [
                    'pattern' => '[0-9+]*',
                    'autocomplete' => 'off',
                    'placeholder' => 'admin.blocklist.list.form.phone.placeholder',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'admin.blocklist.list.form.submit.label',
            ]);
    }
}
