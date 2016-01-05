<?php

namespace LoginCidadao\CoreBundle\EventListener;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use LoginCidadao\NotificationBundle\Helper\NotificationsHelper;
use FOS\UserBundle\Event\FilterUserResponseEvent;

class ChangePasswordListener implements EventSubscriberInterface
{

    private $router;
    private $notificationHelper;

    public function __construct(UrlGeneratorInterface $router,
                                NotificationsHelper $notificationHelper)
    {
        $this->router = $router;
        $this->notificationHelper = $notificationHelper;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::CHANGE_PASSWORD_SUCCESS => 'onChangePasswordSuccess',
            FOSUserEvents::CHANGE_PASSWORD_COMPLETED => 'onChangePasswordCompleted',
        );
    }

    public function onChangePasswordSuccess(FormEvent $event)
    {
        $person = $event->getForm()->getData();
        $this->notificationHelper->clearEmptyPasswordNotification($person);

        $url = $this->router->generate('fos_user_change_password');
        $event->setResponse(new RedirectResponse($url));
    }

    public function onChangePasswordCompleted(FilterUserResponseEvent $event)
    {
        $user = $event->getUser();
        $this->notificationHelper->clearEmptyPasswordNotification($user);
    }

}
