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

use LoginCidadao\PhoneVerificationBundle\Form\SearchPhoneNumberType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;

class SearchPhoneNumberTypeTest extends TestCase
{
    public function testBuildForm()
    {
        /** @var MockObject|FormBuilderInterface $builder */
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->exactly(2))
            ->method('add')
            ->willReturnCallback(function ($name, $type, $options) use ($builder) {
                switch ($name) {
                    case 'phone':
                        $this->checkPhoneSearchField($type, $options);

                        return $builder;
                    case 'submit':
                        $this->checkSubmitField($type, $options);

                        return $builder;
                    default:
                        $this->fail("Unexpected call to add() with name '{$name}'");

                        return null;
                }
            });

        $form = new SearchPhoneNumberType();
        $form->buildForm($builder, []);
    }

    private function checkPhoneSearchField(string $type, array $options)
    {
        $this->assertEquals(TelType::class, $type);
        $this->assertTrue($options['required']);
        $this->assertEquals('admin.blocklist.list.form.phone.label', $options['label']);
        $this->assertEquals('[0-9+]*', $options['attr']['pattern']);
        $this->assertEquals('off', $options['attr']['autocomplete']);
        $this->assertEquals('admin.blocklist.list.form.phone.placeholder', $options['attr']['placeholder']);
    }

    private function checkSubmitField(string $type, array $options)
    {
        $this->assertEquals(SubmitType::class, $type);
        $this->assertEquals('admin.blocklist.list.form.submit.label', $options['label']);
    }
}
