<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Tests\Form\Type;

use LoginCidadao\CoreBundle\Form\Type\ContactFormType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactFormTypeTest extends TestCase
{
    public function testBuildFormWithCaptcha()
    {
        /** @var FormBuilderInterface|\PHPUnit_Framework_MockObject_MockObject $builder */
        $builder = $this->createMock('Symfony\Component\Form\FormBuilderInterface');
        $builder->expects($this->exactly(4))->method('add')->willReturn($builder);

        $form = new ContactFormType(true);
        $form->buildForm($builder, []);
    }

    public function testBuildFormWithoutCaptcha()
    {
        /** @var FormBuilderInterface|\PHPUnit_Framework_MockObject_MockObject $builder */
        $builder = $this->createMock('Symfony\Component\Form\FormBuilderInterface');
        $builder->expects($this->exactly(3))->method('add')->willReturn($builder);

        $form = new ContactFormType(false);
        $form->buildForm($builder, []);
    }

    public function testGetName()
    {
        $this->assertSame('contact_form_type', (new ContactFormType(true))->getName());
    }

    public function testConfigureOptions()
    {
        /** @var OptionsResolver|\PHPUnit_Framework_MockObject_MockObject $resolver */
        $resolver = $this->getMockBuilder('Symfony\Component\OptionsResolver\OptionsResolver')->getMock();
        $resolver->expects($this->once())
            ->method('setDefaults')->with([
                'data_class' => 'LoginCidadao\CoreBundle\Model\SupportMessage',
                'loggedIn' => false,
            ]);

        $form = new ContactFormType(true);
        $form->configureOptions($resolver);
    }
}
