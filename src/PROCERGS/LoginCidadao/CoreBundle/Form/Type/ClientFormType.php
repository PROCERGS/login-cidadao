<?php
namespace PROCERGS\LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\True;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContext;
use OAuth2\OAuth2;

class ClientFormType extends ClientBaseFormType
{
    protected $lcScope;
    
    public function setLcScope($var){
        $this->lcScope = $var;
    }
   
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        /*
        $a = explode(' ', $this->lcScope);
        $a = array_combine($a, $a);
        $builder->add('allowedScopes', 'collection', array(
            'type' => 'choice',
            'options' => array(
                'choices' => $a
            ),
            'required' => true,
            'allow_add' => true
        ));
        $builder->add('allowedGrantTypes', 'collection', array(
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
        */
    }

    public function getName()
    {
        return 'client_form_type';
    }
}
