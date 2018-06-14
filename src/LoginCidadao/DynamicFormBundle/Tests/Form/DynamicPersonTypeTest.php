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

use LoginCidadao\CoreBundle\Entity\Person;
use LoginCidadao\DynamicFormBundle\Form\DynamicPersonType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class DynamicPersonTypeTest extends TestCase
{
    public function testConfigureOptions()
    {
        /** @var OptionsResolver|MockObject $resolver */
        $resolver = $this->createMock('Symfony\Component\OptionsResolver\OptionsResolver');
        $resolver->expects($this->once())->method('setDefaults')->with(
            [
                'data_class' => Person::class,
                'validation_groups' => ['Dynamic'],
                'constraints' => new Valid(),
            ]
        );

        $form = new DynamicPersonType();
        $form->configureOptions($resolver);
    }

    public function testGetName()
    {
        $form = new DynamicPersonType();

        $this->assertEquals('person', $form->getName());
    }
}
