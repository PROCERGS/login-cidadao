<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\True;
use Symfony\Component\Form\AbstractType;

class ContactFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('firstName', 'text',
            array(
            'required' => true,
            'label' => 'form.firstName',
            'mapped' => false
        ))->add('email', 'email',
            array(
            'required' => true,
            'label' => 'form.email',
            'mapped' => false
        ))->add('message', 'textarea',
            array(
            'required' => true,
            'label' => 'form.message',
            'mapped' => false
        ))->add('recaptcha', 'ewz_recaptcha',
            array(
            'attr' => array(
                'options' => array(
                    'theme' => 'clean'
                )
            ),
            'mapped' => false,
            'constraints' => array(
                new True()
            )
        ));
    }

    public function getName()
    {
        return 'contact_form_type';
    }
}
