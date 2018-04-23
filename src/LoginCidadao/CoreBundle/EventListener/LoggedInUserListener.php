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

use LoginCidadao\CoreBundle\Helper\SecurityHelper;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use LoginCidadao\CoreBundle\Model\PersonInterface;

class LoggedInUserListener
{
    /** @var SecurityHelper */
    private $securityHelper;

    /** @var RouterInterface */
    private $router;

    /** @var Session */
    private $session;

    /** @var TranslatorInterface */
    private $translator;

    /** @var boolean */
    private $requireEmailValidation;

    public function __construct(
        SecurityHelper $securityHelper,
        RouterInterface $router,
        Session $session,
        TranslatorInterface $translator,
        $requireEmailValidation
    ) {
        $this->securityHelper = $securityHelper;
        $this->router = $router;
        $this->session = $session;
        $this->translator = $translator;
        $this->requireEmailValidation = $requireEmailValidation;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if ($event->isMasterRequest()
            && $this->securityHelper->hasToken() && false === $this->securityHelper->isOAuthToken()
            && $this->securityHelper->isGranted('IS_AUTHENTICATED_REMEMBERED')
            && $this->securityHelper->getUser() instanceof PersonInterface
        ) {
            $this->checkUnconfirmedEmail($this->securityHelper->getUser());
        }
    }

    private function checkUnconfirmedEmail(PersonInterface $person)
    {
        if (false === $this->requireEmailValidation && !$person->getEmailConfirmedAt() instanceof \DateTime) {
            $params = ['%url%' => $this->router->generate('lc_resend_confirmation_email')];
            $title = $this->translator->trans('notification.unconfirmed.email.title');
            $text = $this->translator->trans('notification.unconfirmed.email.shortText', $params);
            $alert = "<strong>{$title}</strong> {$text}";

            $this->session->getFlashBag()->add('alert.unconfirmed.email', $alert);
        }
    }
}
