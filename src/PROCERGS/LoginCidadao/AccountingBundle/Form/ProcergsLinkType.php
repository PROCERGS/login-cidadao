<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\AccountingBundle\Form;

use PROCERGS\LoginCidadao\AccountingBundle\Entity\ProcergsLink;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProcergsLinkType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'systemType',
                'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
                [
                    'required' => true,
                    'label' => 'admin.accounting.edit.form.system_type.label',
                    'choices' => [
                        ProcergsLink::TYPE_INTERNAL => 'admin.accounting.edit.form.system_type.internal.label',
                        ProcergsLink::TYPE_EXTERNAL => 'admin.accounting.edit.form.system_type.external.label',
                    ],
                ]
            );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'PROCERGS\LoginCidadao\AccountingBundle\Entity\ProcergsLink',
        ]);
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $this->configureOptions($resolver);
    }
}
