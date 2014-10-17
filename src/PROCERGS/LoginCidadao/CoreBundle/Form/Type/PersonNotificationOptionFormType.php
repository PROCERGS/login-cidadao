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

class PersonNotificationOptionFormType extends AbstractType
{

    protected $container;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('sendEmail', 'choice', array(
            'choices'   => array('0' => 'No', '1' => 'Yes'),
            'expanded' => true,
            'required' => true
        ));
        $builder->add('id', 'hidden', array(
            'required' => false,
            'read_only' => true
        ));
    }

    public function getName()
    {
        return 'person_notification_option_form_type';
    }

}
