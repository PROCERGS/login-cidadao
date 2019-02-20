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
use libphonenumber\PhoneNumber;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use FOS\UserBundle\Model\UserInterface;
use LoginCidadao\ValidationBundle\Validator\Constraints\UsernameValidator;

class UserManager extends BaseManager
{
    const FLUSH_STRATEGY_ONCE = 'once';
    const FLUSH_STRATEGY_EACH = 'each';

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
        if (is_null($current) || strlen($current) === 0) {
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
     * @param int $maxIterations
     * @param string $default
     * @return string
     */
    public function getNextAvailableUsername($username, $maxIterations = 10, $default = null)
    {
        $i = 0;
        $testName = $username;

        do {
            $user = $this->findUserByUsername($testName);
        } while ($user !== null && $i < $maxIterations && $testName = $username.$i++);

        if (!$user) {
            return $testName;
        } else {
            return $default ?: "$username@".time();
        }
    }

    public function findUserByUsernameOrEmail($username)
    {
        $onlyNumbers = preg_replace('/[^0-9]/', '', $username);
        $looksLikeCpf = is_numeric($onlyNumbers) && strlen($onlyNumbers) == 11;

        // If it looks like a CPF we give it a try
        if ($looksLikeCpf) {
            $person = parent::findUserBy(['cpf' => $onlyNumbers]);

            if ($person !== null) {
                return $person;
            }
        }

        // If it doesn't look like a CPF number or if we couldn't find nobody with that CPF, fallback to this:
        return parent::findUserByUsernameOrEmail($username);
    }

    public function blockPerson(PersonInterface $person, $andFlush = true)
    {
        if (!$person->isEnabled() && !$person->isAccountNonLocked()) {
            return null;
        }

        $person
            ->setUpdatedAt(new \DateTime())
            ->setEnabled(false)
            ->setPassword('#BLOCKED#'.microtime());
        if (method_exists($person, 'setLocked')) {
            $person->setLocked(true);
        }

        $this->updateUser($person, $andFlush);

        return $person;
    }

    public function blockUsersByPhone(PhoneNumber $phone, $flushStrategy = null)
    {
        $andFlush = $flushStrategy === self::FLUSH_STRATEGY_EACH ? true : false;
        $once = $flushStrategy === self::FLUSH_STRATEGY_ONCE ? true : false;

        /** @var PersonInterface[] $users */
        $users = parent::getRepository()->findBy(['mobile' => $phone]);

        $blockedUsers = [];
        foreach ($users as $user) {
            $user = $this->blockPerson($user, $andFlush);
            if ($user instanceof PersonInterface) {
                $blockedUsers[] = $user;
            }
        }
        if ($once) {
            $this->objectManager->flush();
        }

        return $blockedUsers;
    }
}
