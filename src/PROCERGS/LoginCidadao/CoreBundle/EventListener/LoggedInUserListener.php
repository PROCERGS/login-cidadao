<?php

namespace PROCERGS\LoginCidadao\CoreBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LoggedInUserListener
{

    private $context;
    private $router;

    public function __construct(SecurityContextInterface $context,
                                RouterInterface $router)
    {
        $this->context = $context;
        $this->router = $router;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernel::MASTER_REQUEST != $event->getRequestType()) {
            // don't do anything if it's not the master request
            return;
        }
        if (is_null($this->context->getToken())) {
            return;
        }

        $_route = $event->getRequest()->attributes->get('_route');
        if ($this->context->isGranted('IS_AUTHENTICATED_FULLY') && $_route == 'lc_home') {
            $url = $this->router->generate('fos_user_profile_edit');
            $event->setResponse(new RedirectResponse($url));
        }
    }

}
