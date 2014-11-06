<?php

namespace PROCERGS\LoginCidadao\APIBundle\Event;

use FOS\OAuthServerBundle\Security\Authentication\Token\OAuthToken;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use SimpleThings\EntityAudit\AuditConfiguration;
use PROCERGS\OAuthBundle\Model\ClientUser;
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
        $token = $this->context->getToken();
        if ($token instanceof OAuthToken && $token->getUser() === null) {
            $accessToken = $this->em->
                getRepository('PROCERGSOAuthBundle:AccessToken')->
                findOneBy(array('token' => $token->getToken()));
            $client = $accessToken->getClient();

            $token->setUser(new ClientUser($client));
            $this->auditConfig->setCurrentUsername("OAuthToken:{$token->getToken()}");
        }
    }

}
