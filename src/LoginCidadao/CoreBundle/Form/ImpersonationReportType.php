<?php

namespace LoginCidadao\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ImpersonationReportType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('report', 'textarea',
                array('label' => 'admin.impersonation_report.form.report.label'))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'LoginCidadao\CoreBundle\Entity\ImpersonationReport'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'logincidadao_corebundle_impersonationreport';
    }
}
