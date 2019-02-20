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

use libphonenumber\PhoneNumberFormat;
use LoginCidadao\PhoneVerificationBundle\Form\BlockPhoneFormType;
use LoginCidadao\PhoneVerificationBundle\Model\BlockPhoneNumberRequest;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlockPhoneFormTypeTest extends TestCase
{
    public function testConfigureOptions()
    {
        /** @var MockObject|OptionsResolver $resolver */
        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->once())->method('setDefaults')->with([
            'data_class' => BlockPhoneNumberRequest::class,
        ]);

        $form = new BlockPhoneFormType();
        $form->configureOptions($resolver);
    }

    public function testBuildForm()
    {
        /** @var MockObject|FormBuilderInterface $builder */
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->exactly(2))
            ->method('add')
            ->willReturnCallback(function ($name, $type, $options) use ($builder) {
                switch ($name) {
                    case 'phoneNumber':
                        $this->checkPhoneNumberField($type, $options);

                        return $builder;
                    case 'submit':
                        $this->checkSubmitField($type, $options);

                        return $builder;
                    default:
                        $this->fail("Unexpected call to add() with name '{$name}'");

                        return null;
                }
            });

        $form = new BlockPhoneFormType();
        $form->buildForm($builder, []);
    }

    private function checkPhoneNumberField(string $type, array $options)
    {
        $this->assertEquals(PhoneNumberType::class, $type);
        $this->assertTrue($options['required']);
        $this->assertEquals('admin.blocklist.new.form.phone.label', $options['label']);
        $this->assertStringContainsString('intl-tel', $options['attr']['class']);
        $this->assertEquals('admin.blocklist.new.form.phone.placeholder', $options['attr']['placeholder']);
        $this->assertEquals(PhoneNumberFormat::E164, $options['format']);
    }

    private function checkSubmitField(string $type, array $options)
    {
        $this->assertEquals(SubmitType::class, $type);
        $this->assertEquals('admin.blocklist.new.form.submit.label', $options['label']);
    }
}
