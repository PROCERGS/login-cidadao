<?php
namespace PROCERGS\LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\True;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContext;
use OAuth2\OAuth2;
use PROCERGS\LoginCidadao\CoreBundle\Form\DataTransformer\FromArray;

class ClientBaseFormType extends AbstractType
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
        $builder->add($builder->create('redirecturis', 'textarea', array(
            'required' => true,
        ))->addModelTransformer(new FromArray()) );
        $builder->add('landingpageurl', 'text', array(
            'required' => true
        ));
        $builder->add('termsofuseurl', 'text', array(
            'required' => true
        ));
        $builder->add('pictureFile');
        $builder->add('id', 'hidden', array(
            'required' => false
        ));
    }

    public function getName()
    {
        return 'client_base_form_type';
    }
}
