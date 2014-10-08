<?php

namespace PROCERGS\LoginCidadao\CoreBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Translation\TranslatorInterface;

class LoggedInUserListener
{

    /** @var SecurityContextInterface */
    private $context;

    /** @var RouterInterface */
    private $router;

    /** @var Session */
    private $session;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(SecurityContextInterface $context,
                                RouterInterface $router, Session $session,
                                TranslatorInterface $translator)
    {
        $this->context = $context;
        $this->router = $router;
        $this->session = $session;
        $this->translator = $translator;
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
        if ($this->context->isGranted('IS_AUTHENTICATED_FULLY')) {
            if ($_route == 'lc_home' || $_route == 'fos_user_security_login') {
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
            } else {
                $this->checkUnconfirmedEmail();
            }
        }
    }

    protected function checkUnconfirmedEmail()
    {
        $user = $this->context->getToken()->getUser();
        if (is_null($user->getEmailConfirmedAt())) {
            $params = array('%url%' => $this->router->generate('lc_resend_confirmation_email'));
            $title = $this->translator->trans('notification.unconfirmed.email.title');
            $text = $this->translator->trans('notification.unconfirmed.email.shortText',
                                             $params);
            $alert = sprintf("<strong>%s</strong> %s", $title, $text);
            
            $this->session->getFlashBag()->add('alert.unconfirmed.email', $alert);
        }
    }

}
