<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\AccountingBundle\Tests\Form;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PROCERGS\LoginCidadao\AccountingBundle\Entity\ProcergsLink;
use PROCERGS\LoginCidadao\AccountingBundle\Form\ProcergsLinkType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProcergsLinkTypeTest extends TestCase
{
    public function testBuildForm()
    {
        $fieldOptions = [
            'required' => true,
            'label' => 'admin.accounting.edit.form.system_type.label',
            'choices' => [
                ProcergsLink::TYPE_INTERNAL => 'admin.accounting.edit.form.system_type.internal.label',
                ProcergsLink::TYPE_EXTERNAL => 'admin.accounting.edit.form.system_type.external.label',
            ],
        ];

        /** @var FormBuilderInterface|MockObject $builder */
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->once())->method('add')
            ->with('systemType', ChoiceType::class, $fieldOptions);

        $form = new ProcergsLinkType();
        $form->buildForm($builder, []);
    }

    public function testConfigureOptions()
    {
        /** @var OptionsResolver|MockObject $resolver */
        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->once())->method('setDefaults')
            ->with(['data_class' => ProcergsLink::class]);

        $form = new ProcergsLinkType();
        $form->configureOptions($resolver);
    }
}
