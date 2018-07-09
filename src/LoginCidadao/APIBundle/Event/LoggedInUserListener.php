<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\APIBundle\Event;

use LoginCidadao\OAuthBundle\Entity\AccessTokenRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use FOS\OAuthServerBundle\Security\Authentication\Token\OAuthToken;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use SimpleThings\EntityAudit\AuditConfiguration;
use Symfony\Component\HttpKernel\HttpKernel;
use LoginCidadao\OAuthBundle\Model\ClientUser;

class LoggedInUserListener
{
    /** @var AccessTokenRepository */
    private $accessTokenRepository;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var AuditConfiguration */
    private $auditConfig;

    public function __construct(
        AccessTokenRepository $accessTokenRepository,
        TokenStorageInterface $tokenStorage,
        AuditConfiguration $auditConfig
    ) {
        $this->accessTokenRepository = $accessTokenRepository;
        $this->tokenStorage = $tokenStorage;
        $this->auditConfig = $auditConfig;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernel::MASTER_REQUEST !== $event->getRequestType()) {
            // don't do anything if it's not the master request
            return;
        }

        $token = $this->tokenStorage->getToken();
        if (!$token instanceof OAuthToken) {
            return;
        }

        $this->auditConfig->setCurrentUsername("OAuthToken:{$token->getToken()}");

        // Handling Client Credentials
        if ($token->getUser() === null) {
            $accessToken = $this->accessTokenRepository->findOneBy(['token' => $token->getToken()]);
            $client = $accessToken->getClient();

            $token->setUser(new ClientUser($client));
        }
    }
}
