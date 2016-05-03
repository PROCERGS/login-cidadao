<?php

namespace LoginCidadao\APIBundle\Event;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use FOS\OAuthServerBundle\Security\Authentication\Token\OAuthToken;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use SimpleThings\EntityAudit\AuditConfiguration;
use Symfony\Component\HttpKernel\HttpKernel;
use LoginCidadao\OAuthBundle\Model\ClientUser;
use Doctrine\ORM\EntityManager;

class LoggedInUserListener
{

    /** @var EntityManager */
    private $em;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var AuditConfiguration */
    private $auditConfig;

    public function __construct(EntityManager $em,
                                TokenStorageInterface $tokenStorage,
                                AuditConfiguration $auditConfig)
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
        $this->auditConfig = $auditConfig;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernel::MASTER_REQUEST != $event->getRequestType()) {
            // don't do anything if it's not the master request
            return;
        }
        $token = $this->tokenStorage->getToken();
        if (is_null($token)) {
            return;
        }

        if (!($token instanceof OAuthToken)) {
            return;
        }

        $this->auditConfig->setCurrentUsername("OAuthToken:{$token->getToken()}");

        // Handling Client Credentials
        if ($token->getUser() === null) {
            $accessToken = $this->em->
                getRepository('LoginCidadaoOAuthBundle:AccessToken')->
                findOneBy(array('token' => $token->getToken()));
            $client = $accessToken->getClient();

            $token->setUser(new ClientUser($client));
        }
    }

}
