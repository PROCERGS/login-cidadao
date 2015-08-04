<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Country;
use PROCERGS\LoginCidadao\CoreBundle\Entity\State;
use PROCERGS\LoginCidadao\CoreBundle\Entity\City;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\SecurityContext;

class PlaceholderFormType extends AbstractType
{

    private $security;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text',
                      array(
            'required' => true
        ));
        $builder->add('default', 'text',
                      array(
            'required' => false
        ));
        $builder->add('id', 'hidden',
                      array(
            'required' => false
        ));
        $user = $this->getUser();
        $builder->add('category', 'hidden_entity',
                      array(
            'required' => true,
            'class' => 'PROCERGSLoginCidadaoNotificationBundle:Category',
            'choice_label' => 'name',
            'query_builder' => function (EntityRepository $er) use(&$user) {
                return $er->createQueryBuilder('u')
                        ->join('PROCERGSOAuthBundle:Client', 'c', 'with',
                               'u.client = c')
                        ->where(':person MEMBER OF c.owners')
                        ->setParameter('person', $user)
                        ->orderBy('u.id', 'desc');
            }
        ));
    }

    public function getName()
    {
        return 'placeholder_form_type';
    }

    public function setSecurity(SecurityContext $security)
    {
        $this->security = $security;
        return $this;
    }

    public function getUser()
    {
        if (!$this->security) {
            throw new \LogicException('The SecurityBundle is not registered in your application.');
        }
        if (null === $token = $this->security->getToken()) {
            return;
        }

        if (!is_object($user = $token->getUser())) {
            return;
        }

        return $user;
    }

}
