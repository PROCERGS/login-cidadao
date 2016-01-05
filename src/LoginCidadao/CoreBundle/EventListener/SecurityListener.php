<?php

namespace LoginCidadao\CoreBundle\EventListener;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use LoginCidadao\APIBundle\Security\Audit\ActionLogger;
use LoginCidadao\APIBundle\Security\Audit\Annotation\Loggable;

class SecurityListener
{

    /** @var SecurityContextInterface */
    private $security;

    /** @var Session */
    private $session;

    /** @var ActionLogger */
    private $logger;

    public function __construct(SecurityContextInterface $security,
                                Session $session, ActionLogger $logger)
    {
        $this->security = $security;
        $this->session = $session;
        $this->logger = $logger;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $controllerAction = array($this, 'onSecurityInteractiveLogin');
        $person = $event->getAuthenticationToken()->getUser();
        $this->logger->registerLogin($event->getRequest(), $person, $controllerAction);
    }

}
