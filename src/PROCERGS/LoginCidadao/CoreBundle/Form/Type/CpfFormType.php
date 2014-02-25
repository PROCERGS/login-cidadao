<?php
namespace PROCERGS\LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\True;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContext;

class CpfFormType extends AbstractType
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
        $builder->add('cpf', 'text', array('required' => true, 'label' => 'form.cpf', 'translation_domain' => 'FOSUserBundle'));
        $builder->add('nfgPassword', 'repeated', array(
            'required' => false,
            'type' => 'password',
            'options' => array(
                'attr' => array(
                    'class' => 'form-control'
                ),
                'translation_domain' => 'FOSUserBundle'
            ),
            'first_options' => array(
                'label' => 'form.new_password'
            ),
            'second_options' => array(
                'label' => 'form.new_password_confirmation'
            ),
            'invalid_message' => 'fos_user.password.mismatch',
            'mapped' => false
            
        ));
        $builder->add('save', 'submit');
    }

    public function getName()
    {
        return 'cpf_form_type';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => true
        // 'csrf_field_name' => 'csrf_token',
        // 'intention' => 'authenticate'
                ));
    }
}
