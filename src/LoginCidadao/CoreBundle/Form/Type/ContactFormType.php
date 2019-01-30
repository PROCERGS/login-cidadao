<?php

namespace LoginCidadao\CoreBundle\Form\Type;

use Beelab\Recaptcha2Bundle\Form\Type\RecaptchaType;
use Beelab\Recaptcha2Bundle\Validator\Constraints\Recaptcha2;
use LoginCidadao\CoreBundle\Model\SupportMessage;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'disabled' => $options['loggedIn'],
                'label' => 'contact.form.name.label',
                'attr' => ['placeholder' => 'contact.form.name.placeholder'],
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'disabled' => $options['loggedIn'],
                'label' => 'contact.form.email.label',
                'attr' => ['placeholder' => 'contact.form.email.placeholder'],
            ])
            ->add('message', TextareaType::class, [
                'required' => true,
                'label' => 'contact.form.message.label',
                'attr' => ['placeholder' => 'contact.form.message.placeholder'],
            ]);

        if ($this->enableCaptcha) {
            $recaptchaConstraint = new Recaptcha2();
            $recaptchaConstraint->message = $options['recaptchaError'];
            $builder->add('recaptcha', RecaptchaType::class, [
                'label' => false,
                'mapped' => false,
                'constraints' => $recaptchaConstraint,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'recaptchaError'
        ]);
        $resolver->setDefaults([
            'data_class' => SupportMessage::class,
            'loggedIn' => false,
        ]);
    }


    public function getName()
    {
        return 'contact_form_type';
    }
}
