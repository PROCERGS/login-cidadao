<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Tests\Twig\Extension;

use FOS\UserBundle\Form\Factory\FactoryInterface;
use LoginCidadao\CoreBundle\Twig\Extension\LoginCidadaoExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormFactoryInterface;

class LoginCidadaoExtensionTest extends TestCase
{

    public function testGetFunctions()
    {
        $extension = $this->getLoginCidadaoExtension();

        foreach ($extension->getFunctions() as $function) {
            $this->assertContains($function->getName(), ['lc_getForm', 'lc_getFormFactory']);
            $this->assertContains($function->getCallable()[1], ['getForm', 'getFormFactory']);
        }
    }

    public function testGetFilters()
    {
        $extension = $this->getLoginCidadaoExtension();

        foreach ($extension->getFilters() as $filter) {
            $this->assertContains($filter->getName(), ['formatCep', 'formatCpf']);
            $this->assertContains($filter->getCallable()[1], ['formatCep', 'formatCpf']);
        }
    }

    public function testFunctions()
    {
        $extension = $this->getLoginCidadaoExtension();

        $this->assertSame('login_twig_extension', $extension->getName());
        $this->assertSame('12345-678', $extension->formatCep('12345678'));
        $this->assertSame('123.456.789-01', $extension->formatCpf('12345678901'));
    }

    public function testGetForm()
    {
        $name = 'form_name';

        $form = $this->createMock('Symfony\Component\Form\FormInterface');
        $form->expects($this->once())->method('createView');

        $formFactory = $this->createMock('Symfony\Component\Form\FormFactoryInterface');
        $formFactory->expects($this->once())
            ->method('create')->with($name)
            ->willReturn($form);

        $extension = $this->getLoginCidadaoExtension($formFactory);
        $extension->getForm($name);
    }

    public function testGetFormFactory()
    {
        $form = $this->createMock('Symfony\Component\Form\FormInterface');
        $form->expects($this->once())->method('createView');

        $formFactory = $this->getRegistrationFormFactory();
        $formFactory->expects($this->once())
            ->method('createForm')
            ->willReturn($form);

        $extension = $this->getLoginCidadaoExtension(null, $formFactory);
        $extension->getFormFactory();
    }

    private function getLoginCidadaoExtension($formFactory = null, $registrationFormFactory = null)
    {
        return new LoginCidadaoExtension($formFactory ?: $this->getFormFactory(),
            $registrationFormFactory ?: $this->getRegistrationFormFactory());
    }

    /**
     * @return FactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getRegistrationFormFactory()
    {
        return $this->createMock('FOS\UserBundle\Form\Factory\FactoryInterface');
    }

    /**
     * @return FormFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getFormFactory()
    {
        return $this->createMock('Symfony\Component\Form\FormFactoryInterface');
    }
}
