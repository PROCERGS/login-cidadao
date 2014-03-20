<?php

namespace PROCERGS\LoginCidadao\CoreBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class LoggedInUserListener
{

    private $context;
    private $router;
    private $session;

    public function __construct(SecurityContextInterface $context,
                                RouterInterface $router,
                                SessionInterface $session)
    {
        $this->context = $context;
        $this->router = $router;
        $this->session = $session;
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
            $key = '_security.main.target_path'; #where "main" is your firewall name
            //check if the referer session key has been set
            if ($this->session->has($key)) {
                //set the url based on the link they were trying to access before being authenticated
                $url = $this->session->get($key);

                //remove the session key
                $this->session->remove($key);
            } else {
                $url = $this->router->generate('fos_user_profile_edit');
            }
            $event->setResponse(new RedirectResponse($url));
        }
    }

}
