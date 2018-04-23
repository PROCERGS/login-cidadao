<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\EventListener;

use LoginCidadao\APIBundle\Security\Audit\ActionLogger;
use LoginCidadao\CoreBundle\Helper\SecurityHelper;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Security\Core\Role\SwitchUserRole;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;

class SecurityListener
{
    /** @var SecurityHelper */
    private $securityHelper;

    /** @var ActionLogger */
    private $logger;

    public function __construct(
        SecurityHelper $securityHelper,
        ActionLogger $logger
    ) {
        $this->securityHelper = $securityHelper;
        $this->logger = $logger;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $controllerAction = array($this, 'onSecurityInteractiveLogin');
        $person = $event->getAuthenticationToken()->getUser();
        $this->logger->registerLogin($event->getRequest(), $person, $controllerAction);
    }

    public function onSwitchUser(SwitchUserEvent $event)
    {
        /** @var PersonInterface $target */
        $target = $event->getTargetUser();

        $impersonator = null;
        if ($this->securityHelper->isGranted('ROLE_PREVIOUS_ADMIN')) {
            // Impersonator is going back to normal
            foreach ($this->securityHelper->getTokenRoles() as $role) {
                if ($role instanceof SwitchUserRole) {
                    $impersonator = $role->getSource()->getUser();
                    break;
                }
            }
            $isImpersonating = false;
        } else {
            // Impersonator is becoming the target user
            $impersonator = $this->securityHelper->getUser();
            $isImpersonating = true;
        }

        $controllerAction = array($this, 'onSwitchUser');
        $this->logger->registerImpersonate(
            $event->getRequest(),
            $target,
            $impersonator,
            $controllerAction,
            $isImpersonating
        );
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernel::MASTER_REQUEST === $event->getRequestType()
            && $this->securityHelper->getUser() instanceof PersonInterface
            && $this->securityHelper->isGranted('FEATURE_IMPERSONATION_REPORTS')) {
            $this->checkPendingImpersonateReport();
        }
    }

    private function checkPendingImpersonateReport()
    {
        $person = $this->securityHelper->getUser();
        $this->securityHelper->checkPendingImpersonateReport($person);
    }
}
