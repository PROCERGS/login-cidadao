<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\DynamicFormBundle\Tests\Form;

use LoginCidadao\DynamicFormBundle\Form\DynamicFormType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DynamicFormTypeTest extends TestCase
{
    public function testBuildForm()
    {
        $formService = $this->createMock('LoginCidadao\DynamicFormBundle\Service\DynamicFormServiceInterface');
        $options = ['dynamic_form_service' => $formService];

        /** @var MockObject|FormBuilderInterface $builder */
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->once())->method('addEventSubscriber')
            ->with($this->isInstanceOf('LoginCidadao\DynamicFormBundle\Event\DynamicFormSubscriber'));

        $form = new DynamicFormType();
        $form->buildForm($builder, $options);
    }

    public function testConfigureOptions()
    {
        /** @var MockObject|OptionsResolver $resolver */
        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->once())->method('setRequired')->with('dynamic_form_service');

        $form = new DynamicFormType();
        $form->configureOptions($resolver);
    }
}
