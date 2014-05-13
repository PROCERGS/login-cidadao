<?php
namespace PROCERGS\LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\True;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContext;
use OAuth2\OAuth2;

class ClientFormType extends AbstractType
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

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array(
            'required' => true
        ));
        $builder->add('description', 'textarea', array(
            'required' => true
        ));
        $builder->add('siteurl', 'text', array(
            'required' => true
        ));
        $builder->add('redirecturis', 'collection', array(
            'type' => 'text',
            'options' => array(
                'required' => true
            ),
            'allow_add' => true
        ));
        $a = explode(' ', $this->container->getParameter('lc_supported_scopes'));
        $a = array_combine($a, $a);
        $builder->add('allowedscopes', 'collection', array(
            'type' => 'choice',
            'options' => array(
                'choices' => $a
            ),
            'required' => true,
            'allow_add' => true
        ));
        
        
        $builder->add('allowedgranttypes', 'collection', array(
            'type' => 'choice',
            'options' => array(
                'choices' => array(
                    OAuth2::GRANT_TYPE_AUTH_CODE => 'authorization_code',
                    OAuth2::GRANT_TYPE_IMPLICIT => 'token',
                    OAuth2::GRANT_TYPE_USER_CREDENTIALS => 'password',
                    OAuth2::GRANT_TYPE_CLIENT_CREDENTIALS => 'client_credentials',
                    OAuth2::GRANT_TYPE_REFRESH_TOKEN => 'refresh_token',
                    OAuth2::GRANT_TYPE_EXTENSIONS => 'extensions'
                ),
                'preferred_choices' => array(
                    OAuth2::GRANT_TYPE_AUTH_CODE
                )
            ),
            'required' => true,
            'allow_add' => true
        ));
        
        $builder->add('landingpageurl', 'text', array(
            'required' => true
        ));
        $builder->add('termsofuseurl', 'textarea', array(
            'required' => true
        ));
        $builder->add('pictureFile');
        $builder->add('published', 'checkbox', array(
            'required' => false
        ));
        $builder->add('visible', 'checkbox', array(
            'required' => false
        ));
    }

    public function getName()
    {
        return 'client_form_type';
    }
}
