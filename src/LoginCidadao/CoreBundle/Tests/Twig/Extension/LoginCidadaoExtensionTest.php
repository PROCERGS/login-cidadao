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

use LoginCidadao\CoreBundle\Twig\Extension\LoginCidadaoExtension;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoginCidadaoExtensionTest extends \PHPUnit_Framework_TestCase
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

        $form = $this->getMock('Symfony\Component\Form\FormInterface');
        $form->expects($this->once())->method('createView');

        $formFactory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');
        $formFactory->expects($this->once())
            ->method('create')->with($name)
            ->willReturn($form);

        $container = $this->getContainer();
        $container->expects($this->once())
            ->method('get')->with('form.factory')
            ->willReturn($formFactory);
        $extension = $this->getLoginCidadaoExtension($container);
        $extension->getForm($name);
    }

    public function testGetFormFactory()
    {
        $name = 'form_factory_name';

        $form = $this->getMock('Symfony\Component\Form\FormInterface');
        $form->expects($this->once())->method('createView');

        $formFactory = $this->getMock('FOS\UserBundle\Form\Factory\FactoryInterface');
        $formFactory->expects($this->once())
            ->method('createForm')
            ->willReturn($form);

        $container = $this->getContainer();
        $container->expects($this->once())
            ->method('get')->with($name)
            ->willReturn($formFactory);
        $extension = $this->getLoginCidadaoExtension($container);
        $extension->getFormFactory($name);
    }

    private function getLoginCidadaoExtension(ContainerInterface $container = null)
    {
        return new LoginCidadaoExtension($container ?: $this->getContainer());
    }

    /**
     * @return ContainerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getContainer()
    {
        return $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
    }
}
