<?php

namespace LoginCidadao\CoreBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PlaceholderFormType extends AbstractType
{
    /** @var TokenStorageInterface */
    protected $tokenStorage;

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
            'class' => 'LoginCidadaoNotificationBundle:Category',
            'choice_label' => 'name',
            'query_builder' => function(EntityRepository $er) use(&$user) {
                return $er->createQueryBuilder('u')
                        ->join('LoginCidadaoOAuthBundle:Client', 'c', 'with',
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

    public function setTokenStorage(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
        return $this;
    }

    public function getUser()
    {
        if (!$this->tokenStorage) {
            throw new \LogicException('Token Storage is not available.');
        }
        if (null === $token = $this->tokenStorage->getToken()) {
            return;
        }

        if (!is_object($user = $token->getUser())) {
            return;
        }

        return $user;
    }
}
