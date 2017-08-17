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

use PROCERGS\LoginCidadao\AccountingBundle\Entity\ProcergsLink;
use PROCERGS\LoginCidadao\AccountingBundle\Form\ProcergsLinkType;

class ProcergsLinkTypeTest extends \PHPUnit_Framework_TestCase
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

        $builder = $this->getMock('Symfony\Component\Form\FormBuilderInterface');
        $builder->expects($this->once())->method('add')
            ->with('systemType', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', $fieldOptions);

        $form = new ProcergsLinkType();
        $form->buildForm($builder, []);
    }

    public function testConfigureOptions()
    {
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolver');
        $resolver->expects($this->exactly(2))->method('setDefaults')
            ->with(['data_class' => 'PROCERGS\LoginCidadao\AccountingBundle\Entity\ProcergsLink']);

        $form = new ProcergsLinkType();
        $form->configureOptions($resolver);
        $form->setDefaultOptions($resolver);
    }
}
