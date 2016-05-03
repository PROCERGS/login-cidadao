<?php

namespace LoginCidadao\NotificationBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SettingsType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('clients',
                'Symfony\Component\Form\Extension\Core\Type\CollectionType',
                array(
                'type' => 'LoginCidadao\NotificationBundle\Form\ClientSettingsType',
                'allow_add' => false,
                'allow_delete' => false
            ))
            ->add('save',
                'Symfony\Component\Form\Extension\Core\Type\SubmitType');
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'LoginCidadao\NotificationBundle\Model\NotificationSettings',
            'csrf_protection' => true
        ));
    }
}
