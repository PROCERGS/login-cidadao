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

class DynamicFormTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testBuildForm()
    {
        $formService = $this->getMock('LoginCidadao\DynamicFormBundle\Service\DynamicFormServiceInterface');
        $options = ['dynamic_form_service' => $formService];

        $builder = $this->getMock('Symfony\Component\Form\FormBuilderInterface');
        $builder->expects($this->once())->method('addEventSubscriber')
            ->with($this->isInstanceOf('LoginCidadao\DynamicFormBundle\Event\DynamicFormSubscriber'));

        $form = new DynamicFormType();
        $form->buildForm($builder, $options);
    }

    public function testConfigureOptions()
    {
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolver');
        $resolver->expects($this->once())->method('setRequired')->with('dynamic_form_service');

        $form = new DynamicFormType();
        $form->configureOptions($resolver);
    }
}
