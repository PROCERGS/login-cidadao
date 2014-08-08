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
use Doctrine\ORM\EntityRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ClientNotPerFormType extends AbstractType
{

    protected $container;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('mailsend', 'choice', array(
            'choices'   => array('0' => 'no', '1' => 'yes'),
            'required' => true
        ));
        $builder->add('id', 'hidden', array(
            'required' => false,
            'read_only' => true
        ));
    }

    public function getName()
    {
        return 'client_not_per_form_type';
    }

}
