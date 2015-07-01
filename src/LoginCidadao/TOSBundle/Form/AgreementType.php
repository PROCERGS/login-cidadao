<?php
/*
 *  This file is part of the login-cidadao project or it's bundles.
 *
 *  (c) Guilherme Donato <guilhermednt on github>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace LoginCidadao\TOSBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AgreementType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('agreedAt', new AgreementDateType(),
                array(
                'label' => 'tos.form.agreed_at.label',
                'required' => true,
                'invalid_message' => 'tos.form.agreed_at.error'
                )
            )
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'LoginCidadao\TOSBundle\Entity\Agreement',
            'translation_domain' => 'LoginCidadaoTOSBundle'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'logincidadao_tosbundle_agreement';
    }
}
