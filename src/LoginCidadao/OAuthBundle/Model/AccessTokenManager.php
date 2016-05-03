<?php
/*
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OAuthBundle\Model;

use Doctrine\ORM\EntityManager;
use LoginCidadao\OAuthBundle\Entity\AccessToken;
use FOS\OAuthServerBundle\Security\Authentication\Token\OAuthToken;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AccessTokenManager
{
    /** @var EntityManager */
    private $em;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(EntityManager $em,
                                TokenStorageInterface $tokenStorage)
    {
        $this->em           = $em;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @return boolean
     */
    public function hasToken()
    {
        $securityToken = $this->tokenStorage->getToken();
        return ($securityToken instanceof OAuthToken);
    }

    /**
     * @param string $token
     * @throws AccessDeniedException when no token is found in the current session
     * @return AccessToken
     */
    public function getToken($token = null)
    {
        if ($token === null) {
            $securityToken = $this->tokenStorage->getToken();
            if (!($securityToken instanceof OAuthToken)) {
                throw new AccessDeniedException("Couldn't find an AccessToken in the current session.");
            }
            $token = $securityToken->getToken();
        }
        $repo = $this->em->getRepository('LoginCidadaoOAuthBundle:AccessToken');
        return $repo->findOneByToken($token);
    }

    /**
     * @param string $token defaults to the current AccessToken
     * @return array
     */
    public function getTokenScope($token = null)
    {
        $accessToken = $this->getToken($token);

        $scope = $accessToken->getScope();

        if (is_string($scope)) {
            return explode(' ', $scope);
        } elseif (is_array($scope)) {
            return $scope;
        } else {
            return null;
        }
    }

    /**
     * @param string $token
     * @return ClientInterface
     */
    public function getTokenClient($token = null)
    {
        $accessToken = $this->getToken($token);

        return $accessToken->getClient();
    }
}
