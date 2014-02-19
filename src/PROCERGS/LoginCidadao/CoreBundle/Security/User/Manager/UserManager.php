<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Security\User\Manager;

use FOS\UserBundle\Doctrine\UserManager as BaseManager;
use Doctrine\Common\Persistence\ObjectManager;
use FOS\UserBundle\Util\CanonicalizerInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\DependencyInjection\Container;
use FOS\UserBundle\Model\UserInterface;

class UserManager extends BaseManager
{

    private $container;

    public function __construct(EncoderFactoryInterface $encoderFactory, CanonicalizerInterface $usernameCanonicalizer, CanonicalizerInterface $emailCanonicalizer, ObjectManager $om, $class, Container $container)
    {
        parent::__construct($encoderFactory, $usernameCanonicalizer, $emailCanonicalizer, $om, $class);

        $this->container = $container;
    }

    public function createUser()
    {
        $user = parent::createUser();

        $expityTime = $this->container->getParameter("registration.cpf.empty_time");
        $cpfExpiryDate = new \DateTime($expityTime);
        $user->setCpfExpiration($cpfExpiryDate);

        return $user;
    }

}
