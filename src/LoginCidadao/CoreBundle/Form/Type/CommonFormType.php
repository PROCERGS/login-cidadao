<?php

namespace LoginCidadao\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class CommonFormType extends AbstractType
{

    protected $em;

    /** @var SecurityContext */
    protected $security;
    protected $translator;
    protected $router;

    public function setSecurity(SecurityContext $security)
    {
        $this->security = $security;
        return $this;
    }

    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
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

    public function setTranslator(TranslatorInterface $var)
    {
        $this->translator = $var;
    }

    public function setRouter(RouterInterface $var)
    {
        $this->router = $var;
    }

    public function generateUrl($route, $parameters = array(),
                                $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->router->generate($route, $parameters, $referenceType);
    }

}
