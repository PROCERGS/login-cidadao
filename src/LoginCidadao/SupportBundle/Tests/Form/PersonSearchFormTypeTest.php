<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\SupportBundle\Tests\Form;

use LoginCidadao\SupportBundle\Form\PersonSearchFormType;
use LoginCidadao\SupportBundle\Model\PersonSearchRequest;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonSearchFormTypeTest extends TestCase
{
    public function testForm()
    {
        /** @var OptionsResolver|MockObject $resolver */
        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->once())->method('setDefaults')->with([
            'data_class' => PersonSearchRequest::class,
            'csrf_protection' => false,
        ]);

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->once())->method('setMethod')->with('GET')->willReturn($builder);
        $builder->expects($this->exactly(2))->method('add')
            ->willReturnCallback(function ($name, $type, $options) use ($builder) {
                $this->assertContains($name, ['supportTicket', 'smartSearch']);
                $this->assertEquals(TextType::class, $type);
                if (!empty($options)) {
                    $this->assertArrayHasKey('label', $options);
                }

                return $builder;
            });

        $form = new PersonSearchFormType();
        $form->configureOptions($resolver);
        $form->buildForm($builder, []);
    }
}
