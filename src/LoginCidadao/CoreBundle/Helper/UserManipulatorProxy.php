<?php
/*
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Helper;

use FOS\UserBundle\Util\UserManipulator as BaseClass;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Util\UserManipulator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UserManipulatorProxy extends UserManipulator
{
    /**
     * User manager
     *
     * @var UserManagerInterface
     */
    private $userManager;

    /**
     * UserManipulator constructor.
     *
     * @param UserManagerInterface $userManager
     * @param EventDispatcherInterface $dispatcher
     * @param ContainerInterface $container
     * @internal param UserManipulator $userManipulator
     */
    public function __construct(UserManagerInterface $userManager, EventDispatcherInterface $dispatcher, ContainerInterface $container)
    {
        parent::__construct($userManager, $dispatcher, $container);
        $this->userManager = $userManager;
    }

    /**
     * Creates a user and returns it.
     *
     * @param string $username
     * @param string $password
     * @param string $email
     * @param Boolean $active
     * @param Boolean $superadmin
     *
     * @return \FOS\UserBundle\Model\UserInterface
     */
    public function create($username, $password, $email, $active, $superadmin)
    {
        return parent::create($username, $password, $email, $active, $superadmin);
    }

    /**
     * Activates the given user.
     *
     * @param string $username
     */
    public function activate($username)
    {
        parent::activate($this->findUsername($username));
    }

    /**
     * Deactivates the given user.
     *
     * @param string $username
     */
    public function deactivate($username)
    {
        parent::deactivate($this->findUsername($username));
    }

    /**
     * Changes the password for the given user.
     *
     * @param string $username
     * @param string $password
     */
    public function changePassword($username, $password)
    {
        parent::changePassword($this->findUsername($username), $password);
    }

    /**
     * Promotes the given user.
     *
     * @param string $username
     */
    public function promote($username)
    {
        parent::promote($this->findUsername($username));
    }

    /**
     * Demotes the given user.
     *
     * @param string $username
     */
    public function demote($username)
    {
        parent::demote($this->findUsername($username));
    }

    /**
     * Adds role to the given user.
     *
     * @param string $username
     * @param string $role
     *
     * @return Boolean true if role was added, false if user already had the role
     */
    public function addRole($username, $role)
    {
        return parent::addRole($this->findUsername($username), $role);
    }

    /**
     * Removes role from the given user.
     *
     * @param string $username
     * @param string $role
     *
     * @return Boolean true if role was removed, false if user didn't have the role
     */
    public function removeRole($username, $role)
    {
        return parent::removeRole($this->findUsername($username), $role);
    }

    private function findUsername($usernameOrEmail)
    {
        $user = $this->userManager->findUserByUsernameOrEmail($usernameOrEmail);

        if (!$user) {
            throw new \InvalidArgumentException(sprintf('User identified by "%s" username or email does not exist.', $usernameOrEmail));
        }

        return $user->getUsername();
    }
}
