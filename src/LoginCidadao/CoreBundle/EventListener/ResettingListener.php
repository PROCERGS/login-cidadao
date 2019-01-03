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

use FOS\UserBundle\Event\GetResponseNullableUserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ResettingListener implements EventSubscriberInterface
{
    /** @var UrlGeneratorInterface */
    private $router;

    /** @var int */
    private $tokenTtl;

    public function __construct(UrlGeneratorInterface $router, $tokenTtl)
    {
        $this->router = $router;
        $this->tokenTtl = $tokenTtl;
    }

    public static function getSubscribedEvents()
    {
        return [
            FOSUserEvents::RESETTING_RESET_INITIALIZE => 'onResettingResetInitialize',
            FOSUserEvents::RESETTING_RESET_SUCCESS => 'onResettingResetSuccess',
            FOSUserEvents::RESETTING_SEND_EMAIL_INITIALIZE => 'onResettingEmailRequested',
        ];
    }

    public function onResettingResetInitialize(GetResponseUserEvent $event)
    {
        if (!$event->getUser()->isPasswordRequestNonExpired($this->tokenTtl)) {
            $event->setResponse(new RedirectResponse($this->router->generate('fos_user_resetting_request')));
        }
    }

    public function onResettingResetSuccess(FormEvent $event)
    {
        /** @var $user \FOS\UserBundle\Model\UserInterface */
        $user = $event->getForm()->getData();

        $user->setConfirmationToken(null);
        $user->setPasswordRequestedAt(null);
        $user->setEnabled(true);

        $url = $this->router->generate('fos_user_profile_edit');
        $event->setResponse(new RedirectResponse($url));
    }

    public function onResettingEmailRequested(GetResponseNullableUserEvent $event)
    {
        if (null === $event->getUser()) {
            $event->setResponse(new RedirectResponse($this->router->generate('lc_resetting_user_not_found')));
        }
    }
}
