<?php

namespace LoginCidadao\CoreBundle\EventListener;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;
use Symfony\Component\Security\Core\Role\SwitchUserRole;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use LoginCidadao\APIBundle\Security\Audit\ActionLogger;
use LoginCidadao\CoreBundle\Helper\SecurityHelper;
use LoginCidadao\CoreBundle\Model\PersonInterface;

class SecurityListener
{
    /** @var AuthorizationCheckerInterface */
    private $authChecker;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var SecurityHelper */
    private $securityHelper;

    /** @var ActionLogger */
    private $logger;

    public function __construct(AuthorizationCheckerInterface $authChecker,
                                TokenStorageInterface $tokenStorage,
                                SecurityHelper $securityHelper,
                                ActionLogger $logger)
    {
        $this->tokenStorage   = $tokenStorage;
        $this->authChecker    = $authChecker;
        $this->securityHelper = $securityHelper;
        $this->logger         = $logger;
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
        $target       = $event->getTargetUser();

        if ($authChecker->isGranted('ROLE_PREVIOUS_ADMIN')) {
            // Impersonator is going back to normal
            foreach ($tokenStorage->getToken()->getRoles() as $role) {
                if ($role instanceof SwitchUserRole) {
                    $impersonator = $role->getSource()->getUser();
                    break;
                }
            }
            $isImpersonating = false;
        } else {
            // Impersonator is becoming the target user
            $impersonator    = $this->tokenStorage->getToken()->getUser();
            $isImpersonating = true;
        }

        $controllerAction = array($this, 'onSwitchUser');
        $this->logger->registerImpersonate($event->getRequest(), $target,
            $impersonator, $controllerAction, $isImpersonating);
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

        if ($this->authChecker->isGranted('FEATURE_IMPERSONATION_REPORTS')) {
            if (!($token->getUser() instanceof PersonInterface)) {
                // We don't have a PersonInterface... Nothing to do here.
                return;
            }

            $this->checkPendingImpersonateReport();
        }
    }

    public function checkPendingImpersonateReport()
    {
        $person = $this->tokenStorage->getToken()->getUser();
        $this->securityHelper->checkPendingImpersonateReport($person);
    }
}
