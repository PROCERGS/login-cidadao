<?php
namespace LoginCidadao\CoreBundle\EventListener;

use LoginCidadao\CoreBundle\Security\Exception\AlreadyLinkedAccount;
use LoginCidadao\CoreBundle\Security\Exception\DuplicateEmailException;
use LoginCidadao\CoreBundle\Security\Exception\MissingEmailException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Translation\TranslatorInterface;
use LoginCidadao\CoreBundle\Exception\LcEmailException;
use LoginCidadao\CoreBundle\Exception\LcFcGbException;
use LoginCidadao\CoreBundle\Exception\LcValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExceptionListener
{

    private $session;

    private $router;

    private $translator;

    public function __construct(SessionInterface $session, RouterInterface $router, TranslatorInterface $translator)
    {
        $this->session = $session;
        $this->router = $router;
        $this->translator = $translator;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        if ($exception instanceof AlreadyLinkedAccount) {
            $this->session->getFlashBag()->add('error', $this->translator->trans($exception->getMessage()));
            $url = $this->router->generate('fos_user_profile_edit');
            $event->setResponse(new RedirectResponse($url));
        } elseif ($exception instanceof MissingEmailException) {
            $url = $this->router->generate('task_fill_email', ['service' => $exception->getService()]);
            $event->setResponse(new RedirectResponse($url));
        } elseif ($exception instanceof DuplicateEmailException) {
            $url = $this->router->generate('lc_duplicate_email');
            $event->setResponse(new RedirectResponse($url));
        } elseif ($exception instanceof LcEmailException) {
            $this->session->getFlashBag()->add('error', $this->translator->trans($exception->getMessage()));
            $url = $this->router->generate('lc_home');
            $event->setResponse(new RedirectResponse($url));
        } elseif ($exception instanceof \FacebookApiException) {
            $this->session->getFlashBag()->add('error', $this->translator->trans($exception->getMessage()));
            $url = $this->router->generate('lc_home');
            $event->setResponse(new RedirectResponse($url));
        } elseif ($exception instanceof LcFcGbException) {
            $url = $this->router->generate('lc_link_facebook');
            $event->setResponse(new RedirectResponse($url));
        } elseif ($exception instanceof LcValidationException) {
            $this->session->getFlashBag()->add('error', $this->translator->trans($exception->getMessage()));
            $url = $this->router->generate('fos_user_profile_edit');
            $event->setResponse(new RedirectResponse($url));
        } elseif ($exception instanceof NotFoundHttpException) {
            $request = $event->getRequest();
            $route = $request->get('_route');

            if ($route == 'fos_user_registration_confirm') {
                $this->session->getFlashBag()->add(
                    'error',
                    $this->translator->trans('This e-mail is already confirmed.')
                );
                $url = $this->router->generate('fos_user_profile_edit');
                $event->setResponse(new RedirectResponse($url));
            }
        }
    }
}
