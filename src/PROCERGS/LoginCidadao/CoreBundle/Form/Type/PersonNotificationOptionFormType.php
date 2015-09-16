<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

class PersonNotificationOptionFormType extends AbstractType
{
    protected $container;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('sendEmail', 'choice',
            array(
            'choices' => array('0' => 'No', '1' => 'Yes'),
            'expanded' => true,
            'required' => true
        ))->add('id', 'hidden',
            array(
            'required' => false,
            'read_only' => true
        ));
    }

    public function getName()
    {
        return 'person_notification_option_form_type';
    }
}
