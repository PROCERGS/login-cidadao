<?php

namespace LoginCidadao\CoreBundle\Security\User\Manager;

use FOS\UserBundle\Doctrine\UserManager as BaseManager;
use Doctrine\Common\Persistence\ObjectManager;
use FOS\UserBundle\Util\CanonicalizerInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\DependencyInjection\Container;
use FOS\UserBundle\Model\UserInterface;
use LoginCidadao\ValidationBundle\Validator\Constraints\UsernameValidator;

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
     * Updates a user.
     *
     * @param UserInterface $user
     * @param Boolean       $andFlush Whether to flush the changes (default true)
     */
    public function updateUser(UserInterface $user, $andFlush = true)
    {
        $this->updateCanonicalFields($user);
        $this->enforceUsername($user);

        parent::updateUser($user, $andFlush);
    }

    /**
     * Enforces that the given user will have an username
     * @param \FOS\UserBundle\Model\UserInterface $user
     */
    public function enforceUsername(UserInterface $user)
    {
        $current = $user->getUsernameCanonical();
        if (is_null($current) || strlen($current) == 0) {
            $email = explode('@', $user->getEmailCanonical(), 2);
            $username = $email[0];
            if (!UsernameValidator::isUsernameValid($username)) {
                $username = UsernameValidator::getValidUsername();
            }
            $newUsername = $this->getNextAvailableUsername($username);

            $user->setUsername($newUsername);
            $this->updateCanonicalFields($user);
        }
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

    public function findUserByUsernameOrEmail($username) {
       if (is_numeric($cpf = preg_replace('/[^0-9]/', '', $username)) && strlen($cpf) == 11) {
            $person = parent::findUserBy(array('cpf' => $cpf));
            if ($person !== null) {
                return $person;
            }
        } 
        return parent::findUserByUsernameOrEmail($username);        
    }
}