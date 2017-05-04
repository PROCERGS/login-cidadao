<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\PhoneVerificationBundle\Tests\Form;

use LoginCidadao\PhoneVerificationBundle\Form\PhoneVerificationType;

class PhoneVerificationTypeTest extends \PHPUnit_Framework_TestCase
{
    private function getOptions($useUpper, $useLower)
    {
        $optionsClass = 'LoginCidadao\PhoneVerificationBundle\Service\PhoneVerificationOptions';
        $options = $this->getMockBuilder($optionsClass)
            ->disableOriginalConstructor()
            ->getMock();
        $options->expects($this->any())->method('isUseUpperCase')->willReturn($useUpper);
        $options->expects($this->any())->method('isUseLowerCase')->willReturn($useLower);

        return $options;
    }

    private function getBuilder($expectedType)
    {
        $builder = $this->getMock('Symfony\Component\Form\FormBuilderInterface');
        $builder->expects($this->once())->method('add')->with(
            $this->equalTo('verificationCode'),
            $this->equalTo($expectedType)
        );

        return $builder;
    }

    public function testForm()
    {
        $text = 'Symfony\Component\Form\Extension\Core\Type\TextType';
        $tel = 'LoginCidadao\CoreBundle\Form\Type\TelType';

        $textOptions = [
            $this->getOptions(true, true),
            $this->getOptions(true, false),
            $this->getOptions(false, true),
        ];
        $numberOptions = $this->getOptions(false, false);

        foreach ($textOptions as $options) {
            $builder = $this->getBuilder($text);
            $form = new PhoneVerificationType($options);
            $form->buildForm($builder, []);
        }

        $numberBuilder = $this->getBuilder($tel);
        $form = new PhoneVerificationType($numberOptions);
        $form->buildForm($numberBuilder, []);
    }
}
