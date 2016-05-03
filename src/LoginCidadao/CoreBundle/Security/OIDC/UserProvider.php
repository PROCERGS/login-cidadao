<?php
/*
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Security\OIDC;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use OAuth2\ServerBundle\User\OAuth2UserProvider;

class UserProvider extends OAuth2UserProvider implements UserProviderInterface
{
    private $em;
    private $encoderFactory;

    public function __construct(EntityManager $entityManager,
                                EncoderFactoryInterface $encoderFactory)
    {
        $this->em             = $entityManager;
        $this->encoderFactory = $encoderFactory;
    }

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $username The username
     *
     * @return UserInterface
     *
     * @see UsernameNotFoundException
     *
     * @throws UsernameNotFoundException if the user is not found
     *
     */
    public function loadUserByUsername($username)
    {
        $user = $this->em->getRepository('LoginCidadaoCoreBundle:Person')
            ->find($username);

        if (!$user) {
            throw new UsernameNotFoundException(sprintf(
                'Username "%s" not found.', $username
            ));
        }

        return $user;
    }

    /**
     * Refreshes the user for the account interface.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     * @param UserInterface $user
     *
     * @return UserInterface
     *
     * @throws UnsupportedUserException if the account is not supported
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof OAuth2User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.',
                get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * Whether this provider supports the given user class
     *
     * @param string $class
     *
     * @return Boolean
     */
    public function supportsClass($class)
    {
        if ($class == 'Person') {
            return true;
        }

        return false;
    }

    /**
     * Creates a new user
     *
     * @param string $username
     *
     * @param string $password
     *
     * @param array $roles
     *
     * @param array $scopes
     *
     * @return UserInterface
     */
    public function createUser($username, $password, array $roles = array(),
                                array $scopes = array())
    {
        $user = parent::createUser();

        // Generate password
        $salt     = $this->generateSalt();
        $password = $this->encoderFactory->getEncoder($user)
            ->encodePassword($password, $salt);

        $user->setSalt($salt);
        $user->setPassword($password);
        $user->setUsername($username);
        $user->setRoles($roles);
        $user->setScopes($scopes);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}
