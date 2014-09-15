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

class CategoryFormType extends AbstractType
{

    protected $container;

    public function setContainer(ContainerInterface $var)
    {
        $this->container = $var;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $person = $this->getUser();
        $builder->add('client', 'entity', array(
            'required' => true,
            'class' => 'PROCERGSOAuthBundle:Client',
            'property' => 'name',
            'query_builder' => function (EntityRepository $er) use($person)
            {
                return $er->createQueryBuilder('u')
                    ->where('u.person = :person')
                    ->setParameter('person', $person)
                    ->orderBy('u.name', 'ASC');
            }
        ));
        $builder->add('name', 'text', array(
            'required' => true
        ));
        $builder->add('defaulticon', 'choice', array(
            'choices'   => array('glyphicon glyphicon-envelope' => 'envelope', 'glyphicon glyphicon-exclamation-sign' => 'exclamation-sign'),
            'required' => true
        ));
        $builder->add('defaulttitle', 'text', array(
            'required' => true
        ));
        $builder->add('defaultshorttext', 'text', array(
            'required' => true
        ));
        $builder->add('mailtemplate', 'textarea', array(
            'required' => true,
        ));
        $builder->add('mailsenderaddress', 'text', array(
            'required' => true
        ));
        $builder->add('emailable', 'choice', array(
            'choices'   => array('0' => 'no', '1' => 'yes'),
            'required' => true
        ));
        $builder->add('id', 'integer', array(
            'required' => false,
            'read_only' => true
        ));
        $builder->add('markdowntemplate', 'textarea', array(
            'required' => true,
        ));
    }

    public function getName()
    {
        return 'category_form_type';
    }

    public function getUser()
    {
        if (! $this->container->has('security.context')) {
            throw new \LogicException('The SecurityBundle is not registered in your application.');
        }
        
        if (null === $token = $this->container->get('security.context')->getToken()) {
            return;
        }
        
        if (! is_object($user = $token->getUser())) {
            return;
        }
        
        return $user;
    }
}
