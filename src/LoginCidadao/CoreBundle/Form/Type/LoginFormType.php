<?php

namespace LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\True;

class LoginFormType extends AbstractType
{
    protected $container;

    public function setContainer($var)
    {
        $this->container = $var;
    }

    public function getContainer()
    {
        return $this->container;
    }
    private $verifyCaptcha;

    public function hasVerifyCaptcha()
    {
        if ($this->verifyCaptcha === null) {
            $request = $this->container->get('request');
            $session = $request->getSession();
            if (null !== $session) {
                $lastUsername        = $session->get(Security::LAST_USERNAME);
                $doctrine            = $this->container->get('doctrine');
                $vars                = array(
                    'ip' => $request->getClientIp(),
                    'username' => $lastUsername
                );
                $accessSession       = $doctrine->getRepository('LoginCidadaoCoreBundle:AccessSession')->findOneBy($vars);
                $this->verifyCaptcha = ($accessSession && $accessSession->getVal()
                    >= $this->container->getParameter('brute_force_threshold'));
            }
        }
        return $this->verifyCaptcha;
    }

    public function setVerifyCaptcha($var)
    {
        $this->verifyCaptcha = $var;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->setVerifyCaptcha($options['check_captcha']);

        $builder->add('username',
            'Symfony\Component\Form\Extension\Core\Type\TextType',
            array(
            'label' => 'security.login.username',
        ));
        $builder->add('password',
            'Symfony\Component\Form\Extension\Core\Type\PasswordType',
            array(
            'label' => 'security.login.password',
            'attr' => array('autocomplete' => 'off'),
            'mapped' => false
        ));

        if ($this->hasVerifyCaptcha()) {
            $builder->add('recaptcha',
                'EWZ\Bundle\RecaptchaBundle\Form\Type\RecaptchaType',
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
    }

    public function getBlockPrefix()
    {
        return 'login_form_type';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => true,
            'csrf_field_name' => 'csrf_token',
            'csrf_token_id' => 'authenticate',
            'check_captcha' => null
        ));
    }
}
