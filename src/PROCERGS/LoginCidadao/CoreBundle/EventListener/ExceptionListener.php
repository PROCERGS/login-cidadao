<?php

namespace PROCERGS\LoginCidadao\CoreBundle\EventListener;

use PROCERGS\LoginCidadao\CoreBundle\Security\Exception\AlreadyLinkedAccount;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;


class ExceptionListener
{

    private $session;
    private $router;

    public function __construct(SessionInterface $session, RouterInterface $router)
    {
        $this->session = $session;
        $this->router = $router;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        if ($exception instanceof AlreadyLinkedAccount) {
            $this->session->getFlashBag()->add(
                'error',
                $exception->getMessage()
            );
            $url = $this->router->generate('fos_user_profile_edit');
            $event->setResponse(new RedirectResponse($url));
        }
    }
}
