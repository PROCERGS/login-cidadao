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
use LoginCidadao\CoreBundle\Entity\Person;
use LoginCidadao\PhoneVerificationBundle\Form\PhoneNumberFormType;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PhoneNumberFormTypeTest extends TestCase
{
    public function testForm()
    {
        /** @var MockObject|FormBuilderInterface $builder */
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->once())->method('add')->with(
            $this->equalTo('mobile'),
            $this->equalTo(PhoneNumberType::class),
            $this->equalTo([
                'required' => false,
                'label' => 'person.form.mobile.label',
                'attr' => [
                    'class' => 'form-control intl-tel',
                    'placeholder' => 'person.form.mobile.placeholder',
                ],
                'label_attr' => ['class' => 'intl-tel-label'],
                'format' => PhoneNumberFormat::E164,
            ])
        );

        /** @var MockObject|OptionsResolver $resolver */
        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->once())->method('setDefaults')->with([
            'data_class' => Person::class,
            'validation_groups' => ['LoginCidadaoEmailForm'],
        ]);

        $form = new PhoneNumberFormType();
        $form->configureOptions($resolver);
        $form->buildForm($builder, []);
    }
}
