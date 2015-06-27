<?php

namespace PROCERGS\LoginCidadao\NotificationBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientSettingsType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('options', 'collection',
            array(
            'type' => new PersonNotificationOptionType(),
            'allow_add' => false,
            'allow_delete' => false
        ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PROCERGS\LoginCidadao\NotificationBundle\Model\ClientSettings',
            'csrf_protection' => true
        ));
    }

    public function getName()
    {
        return 'settings';
    }
}
