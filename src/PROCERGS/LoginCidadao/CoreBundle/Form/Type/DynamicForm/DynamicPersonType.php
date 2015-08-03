<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Form\Type\DynamicForm;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Person;

class DynamicPersonType extends AbstractType
{

    /**
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
    }

    /**
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            //'data_class' => 'PROCERGS\LoginCidadao\CoreBundle\Model\DynamicFormData'
            'data_class' => 'PROCERGS\LoginCidadao\CoreBundle\Entity\Person'
        ));
    }

    /**
     *
     * @return string
     */
    public function getName()
    {
        return 'person';
    }
}
