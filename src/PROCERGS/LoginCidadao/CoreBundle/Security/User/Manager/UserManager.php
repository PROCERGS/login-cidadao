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

    public function __construct(EncoderFactoryInterface $encoderFactory,
                                CanonicalizerInterface $usernameCanonicalizer,
                                CanonicalizerInterface $emailCanonicalizer,
                                ObjectManager $om, $class, Container $container)
    {
        parent::__construct($encoderFactory, $usernameCanonicalizer,
                $emailCanonicalizer, $om, $class);

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

    /**
     * Tries to find an available username.
     * TODO: Yeah, this is ugly, I'm sorry, but does the job.
     * This is based on HWI's FOSUBRegistrationFormHandler
     *
     * @param string $username
     * @param integer $username
     * @return string
     */
    public function getNextAvailableUsername($username, $maxIterations = 10,
                                             $default = null)
    {
        $i = 0;
        $testName = $username;

        do {
            $user = $this->findUserByUsername($testName);
        } while ($user !== null && $i < $maxIterations && $testName = $username . $i++);

        if (is_null($user)) {
            return $testName;
        } else {
            if (is_null($default)) {
                return "$username@" . time();
            } else {
                return $default;
            }
        }
    }

}
