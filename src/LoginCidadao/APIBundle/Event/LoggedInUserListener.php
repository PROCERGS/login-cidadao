<?php

namespace LoginCidadao\APIBundle\Event;

use FOS\OAuthServerBundle\Security\Authentication\Token\OAuthToken;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use SimpleThings\EntityAudit\AuditConfiguration;
use Symfony\Component\HttpKernel\HttpKernel;
use LoginCidadao\OAuthBundle\Model\ClientUser;
use Doctrine\ORM\EntityManager;

class LoggedInUserListener
{

    /** @var EntityManager */
    private $em;

    /** @var SecurityContextInterface */
    private $context;

    /** @var AuditConfiguration */
    private $auditConfig;

    public function __construct(EntityManager $em,
                                SecurityContextInterface $context,
                                AuditConfiguration $auditConfig)
    {
        $this->em = $em;
        $this->context = $context;
        $this->auditConfig = $auditConfig;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernel::MASTER_REQUEST != $event->getRequestType()) {
            // don't do anything if it's not the master request
            return;
        }
        $token = $this->context->getToken();
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
