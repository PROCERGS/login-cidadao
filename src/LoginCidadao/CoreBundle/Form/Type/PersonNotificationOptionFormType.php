<?php

namespace LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

class PersonNotificationOptionFormType extends AbstractType
{
    protected $container;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('sendEmail',
            'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
            array(
            'choices' => array('0' => 'No', '1' => 'Yes'),
            'expanded' => true,
            'required' => true
        ))->add('id', 'Symfony\Component\Form\Extension\Core\Type\HiddenType',
            array(
            'required' => false,
            'read_only' => true
        ));
    }
}
