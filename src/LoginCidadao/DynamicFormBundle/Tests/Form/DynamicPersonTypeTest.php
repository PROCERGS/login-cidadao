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

use LoginCidadao\DynamicFormBundle\Form\DynamicPersonType;
use Symfony\Component\Validator\Constraints\Valid;

class DynamicPersonTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigureOptions()
    {
        $personClass = 'LoginCidadao\CoreBundle\Entity\Person';
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolver');
        $resolver->expects($this->once())->method('setDefaults')->with(
            [
                'data_class' => $personClass,
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
