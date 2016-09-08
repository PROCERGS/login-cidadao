<?php

namespace LoginCidadao\CoreBundle\Form\Type\DynamicForm;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

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
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'LoginCidadao\CoreBundle\Entity\Person',
            'validation_groups' => ['Dynamic'],
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
