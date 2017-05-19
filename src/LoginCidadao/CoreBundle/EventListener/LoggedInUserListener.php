<?php

namespace LoginCidadao\CoreBundle\EventListener;

use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use FOS\OAuthServerBundle\Security\Authentication\Token\OAuthToken;
use LoginCidadao\CoreBundle\Exception\RedirectResponseException;
use LoginCidadao\CoreBundle\Model\PersonInterface;

class LoggedInUserListener
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var AuthorizationCheckerInterface */
    private $authChecker;

    /** @var RouterInterface */
    private $router;

    /** @var Session */
    private $session;

    /** @var TranslatorInterface */
    private $translator;

    /** @var boolean */
    private $requireEmailValidation;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authChecker,
        RouterInterface $router,
        Session $session,
        TranslatorInterface $translator,
        $requireEmailValidation
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authChecker = $authChecker;
        $this->router = $router;
        $this->session = $session;
        $this->translator = $translator;

        $this->requireEmailValidation = $requireEmailValidation;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernel::MASTER_REQUEST != $event->getRequestType()) {
            // don't do anything if it's not the master request
            return;
        }
        $token = $this->tokenStorage->getToken();

        if (is_null($token) || $token instanceof OAuthToken ||
            $this->authChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') === false
        ) {
            return;
        }
        if (!($token->getUser() instanceof PersonInterface)) {
            // We don't have a PersonInterface... Nothing to do here.
            return;
        }

        try {
            $this->checkUnconfirmedEmail();
        } catch (RedirectResponseException $e) {
            $event->setResponse($e->getResponse());
        }
    }

    protected function checkUnconfirmedEmail()
    {
        if ($this->requireEmailValidation) {
            // There is a Task for that already
            return;
        }
        $token = $this->tokenStorage->getToken();
        $user = $token->getUser();
        if (is_null($user->getEmailConfirmedAt())) {
            $params = array('%url%' => $this->router->generate('lc_resend_confirmation_email'));
            $title = $this->translator->trans('notification.unconfirmed.email.title');
            $text = $this->translator->trans(
                'notification.unconfirmed.email.shortText',
                $params
            );
            $alert = "<strong>{$title}</strong> {$text}";

            $this->session->getFlashBag()->add('alert.unconfirmed.email', $alert);
        }
    }
}
