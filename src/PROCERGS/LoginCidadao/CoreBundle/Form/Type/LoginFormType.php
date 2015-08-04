<?php
namespace PROCERGS\LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\True;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\SecurityContext;

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
                $lastUsername = $session->get(SecurityContext::LAST_USERNAME);
                $doctrine = $this->container->get('doctrine');
                $vars = array(
                    'ip' => $request->getClientIp(),
                    'username' => $lastUsername
                );
                $accessSession = $doctrine->getRepository('PROCERGSLoginCidadaoCoreBundle:AccessSession')->findOneBy($vars);
                $this->verifyCaptcha = ($accessSession && $accessSession->getVal() >= $this->container->getParameter('brute_force_threshold'));
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
        $builder->add('username', 'text', array(
            'label' => 'security.login.username',
            //'translation_domain' => 'PROCERGSLoginCidadaoCoreBundle',
            //'attr' => array('placeholder' => 'security.login.username')
        ));
        $builder->add('password', 'password', array(
            'label' => 'security.login.password',
            //'translation_domain' => 'PROCERGSLoginCidadaoCoreBundle',
            'mapped' => false
        ));

         if ($this->hasVerifyCaptcha()) {
            $builder->add('recaptcha', 'ewz_recaptcha', array(
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

    public function getName()
    {
        return 'login_form_type';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => true,
            'csrf_field_name' => 'csrf_token',
            'intention' => 'authenticate'
        ));
    }
}
