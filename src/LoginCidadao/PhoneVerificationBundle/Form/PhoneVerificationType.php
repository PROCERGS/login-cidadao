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

use LoginCidadao\PhoneVerificationBundle\Service\PhoneVerificationOptions;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PhoneVerificationType extends AbstractType
{
    /** @var PhoneVerificationOptions */
    private $phoneVerificationOptions;

    /**
     * PhoneVerificationType constructor.
     * @param PhoneVerificationOptions $phoneVerificationOptions
     */
    public function __construct(PhoneVerificationOptions $phoneVerificationOptions)
    {
        $this->phoneVerificationOptions = $phoneVerificationOptions;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $useLetters = $this->phoneVerificationOptions->isUseUpperCase()
            || $this->phoneVerificationOptions->isUseLowerCase();

        if ($useLetters) {
            $type = 'Symfony\Component\Form\Extension\Core\Type\TextType';
        } else {
            $type = 'LoginCidadao\CoreBundle\Form\Type\TelType';
        }

        $builder->add(
            'verificationCode',
            $type,
            [
                'label' => 'tasks.verify_phone.form.verificationCode.label',
                'attr' => [
                    'placeholder' => 'tasks.verify_phone.form.verificationCode.placeholder',
                ],
            ]
        );
    }
}
