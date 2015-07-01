<?php

namespace LoginCidadao\TOSBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TermsOfServiceType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('terms', null,
                array(
                'label' => 'tos.form.terms.label'
            ))
            ->add('final', null,
                array(
                'label' => 'tos.form.final.label',
                'required' => false
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'LoginCidadao\TOSBundle\Entity\TermsOfService'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tos_termsofservice';
    }
}
