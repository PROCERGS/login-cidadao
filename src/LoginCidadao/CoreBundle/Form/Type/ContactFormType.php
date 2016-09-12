<?php

namespace LoginCidadao\CoreBundle\Form\Type;

use Beelab\Recaptcha2Bundle\Validator\Constraints\Recaptcha2;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactFormType extends AbstractType
{
    /** @var boolean */
    private $enableCaptcha;

    public function __construct($enableCaptcha = true)
    {
        $this->enableCaptcha = $enableCaptcha;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            'text',
            array(
                'required' => true,
                'label' => 'form.firstName',
            )
        )->add(
            'email',
            'email',
            array(
                'required' => true,
                'label' => 'form.email',
            )
        )->add(
            'message',
            'Symfony\Component\Form\Extension\Core\Type\TextareaType',
            array(
                'required' => true,
                'label' => 'form.message',
            )
        );

        if ($this->enableCaptcha) {
            $builder->add(
                'recaptcha',
                'Beelab\Recaptcha2Bundle\Form\Type\RecaptchaType',
                [
                    'label' => false,
                    'mapped' => false,
                    'constraints' => new Recaptcha2(['groups' => ['LoginCidadaoRegistration', 'Registration']]),
                ]
            );
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'LoginCidadao\CoreBundle\Model\SupportMessage',
            ]
        );
    }


    public function getName()
    {
        return 'contact_form_type';
    }
}
