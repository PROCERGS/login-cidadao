<?php

namespace LoginCidadao\CoreBundle\EventListener;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use LoginCidadao\NotificationBundle\Helper\NotificationsHelper;
use FOS\UserBundle\Event\FilterUserResponseEvent;

class ChangePasswordListener implements EventSubscriberInterface
{
    /** @var UrlGeneratorInterface */
    private $router;

    /** @var NotificationsHelper */
    private $notificationHelper;

    /** @var SessionInterface */
    private $session;

    /** @var string */
    private $defaultPasswordEncoder;

    public function __construct(UrlGeneratorInterface $router,
                                NotificationsHelper $notificationHelper,
                                SessionInterface $session,
                                $defaultPasswordEncoder)
    {
        $this->router                 = $router;
        $this->notificationHelper     = $notificationHelper;
        $this->session                = $session;
        $this->defaultPasswordEncoder = $defaultPasswordEncoder;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::CHANGE_PASSWORD_SUCCESS => 'onChangePasswordSuccess',
            FOSUserEvents::RESETTING_RESET_SUCCESS => 'setPasswordEncoderName',
            FOSUserEvents::REGISTRATION_SUCCESS => 'setPasswordEncoderName',
            FOSUserEvents::CHANGE_PASSWORD_COMPLETED => 'onChangePasswordCompleted',
        );
    }

    public function onChangePasswordSuccess(FormEvent $event)
    {
        $this->setPasswordEncoderName($event);
        $this->clearNotification($event);
        $this->redirectToPasswordChangePage($event);
    }

    private function redirectToPasswordChangePage(FormEvent $event)
    {
        $url = $this->router->generate('fos_user_change_password');
        $event->setResponse(new RedirectResponse($url));
    }

    private function clearNotification(FormEvent $event)
    {
        $person = $event->getForm()->getData();
        $this->notificationHelper->clearEmptyPasswordNotification($person);
    }

    public function setPasswordEncoderName(FormEvent $event)
    {
        $person = $event->getForm()->getData();
        $person->setPasswordEncoderName($this->defaultPasswordEncoder);
        $this->session->remove('force_password_change');
    }

    public function onChangePasswordCompleted(FilterUserResponseEvent $event)
    {
        $user = $event->getUser();
        $this->notificationHelper->clearEmptyPasswordNotification($user);
    }
}
