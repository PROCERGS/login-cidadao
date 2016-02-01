<?php

namespace LoginCidadao\CoreBundle\EventListener;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;
use Symfony\Component\HttpFoundation\Session\Session;
use LoginCidadao\APIBundle\Security\Audit\ActionLogger;
use LoginCidadao\APIBundle\Security\Audit\Annotation\Loggable;

class SecurityListener
{
    /** @var AuthorizationCheckerInterface */
    private $authChecker;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var Session */
    private $session;

    /** @var ActionLogger */
    private $logger;

    public function __construct(AuthorizationCheckerInterface $authChecker,
                                TokenStorageInterface $tokenStorage,
                                Session $session, ActionLogger $logger)
    {
        $this->tokenStorage = $tokenStorage;
        $this->authChecker  = $authChecker;
        $this->session      = $session;
        $this->logger       = $logger;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $controllerAction = array($this, 'onSecurityInteractiveLogin');
        $person           = $event->getAuthenticationToken()->getUser();
        $this->logger->registerLogin($event->getRequest(), $person,
            $controllerAction);
    }

    public function onSwitchUser(SwitchUserEvent $event)
    {
        $tokenStorage = $this->tokenStorage;
        $authChecker  = $this->authChecker;

        if ($authChecker->isGranted('ROLE_PREVIOUS_ADMIN')) {
            foreach ($tokenStorage->getToken()->getRoles() as $role) {
                if ($role instanceof SwitchUserRole) {
                    $impersonatingUser = $role->getSource()->getUser();
                    break;
                }
            }
            $impersonatingUser;
        }
    }
}
