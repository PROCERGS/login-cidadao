<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Security\User\Manager;

use FOS\UserBundle\Doctrine\UserManager as BaseManager;
use Doctrine\Common\Persistence\ObjectManager;
use FOS\UserBundle\Util\CanonicalizerInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use FOS\UserBundle\Model\UserInterface;
use LoginCidadao\ValidationBundle\Validator\Constraints\UsernameValidator;

class UserManager extends BaseManager
{
    public function __construct(
        EncoderFactoryInterface $encoderFactory,
        CanonicalizerInterface $usernameCanonicalizer,
        CanonicalizerInterface $emailCanonicalizer,
        ObjectManager $om,
        $class
    ) {
        parent::__construct($encoderFactory, $usernameCanonicalizer,
            $emailCanonicalizer, $om, $class);
    }

    public function createUser()
    {
        return parent::createUser();
    }

    /**
     * Updates a user.
     *
     * @param UserInterface $user
     * @param Boolean $andFlush Whether to flush the changes (default true)
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
     * This is based on HWI's FOSUBRegistrationFormHandler
     *
     * @param string $username
     * @param int $maxIterations
     * @param null $default
     * @return string
     */
    public function getNextAvailableUsername($username, $maxIterations = 10, $default = null)
    {
        $i = 0;
        $testName = $username;

        do {
            $user = $this->findUserByUsername($testName);
        } while ($user !== null && $i < $maxIterations && $testName = $username.$i++);

        if (is_null($user)) {
            return $testName;
        } else {
            if (is_null($default)) {
                return "$username@".time();
            } else {
                return $default;
            }
        }
    }

    public function findUserByUsernameOrEmail($username)
    {
        $cpf = preg_replace('/[^0-9]/', '', $username);
        if (is_numeric($cpf) && strlen($cpf) == 11) {
            $person = parent::findUserBy(['cpf' => $cpf]);
            if ($person !== null) {
                return $person;
            }
        }

        return parent::findUserByUsernameOrEmail($username);
    }
}
