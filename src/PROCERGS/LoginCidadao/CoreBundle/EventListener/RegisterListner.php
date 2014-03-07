<?php

namespace PROCERGS\LoginCidadao\CoreBundle\EventListener;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use PROCERGS\LoginCidadao\CoreBundle\Helper\NotificationsHelper;

class RegisterListner implements EventSubscriberInterface
{

    private $router;
    private $mailer;
    private $tokenGenerator;
    private $notificationHelper;

    public function __construct(UrlGeneratorInterface $router,
                                MailerInterface $mailer,
                                TokenGeneratorInterface $tokenGenerator,
                                NotificationsHelper $notificationHelper)
    {
        $this->router = $router;
        $this->mailer = $mailer;
        $this->tokenGenerator = $tokenGenerator;
        $this->notificationHelper = $notificationHelper;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::REGISTRATION_SUCCESS => 'onRegistrationSuccess',
            FOSUserEvents::REGISTRATION_COMPLETED => 'onRegistrationCompleted',
            FOSUserEvents::REGISTRATION_CONFIRM => 'onEmailConfirmed'
        );
    }

    public function onRegistrationSuccess(FormEvent $event)
    {
        $user = $event->getForm()->getData();

        if (null === $user->getConfirmationToken()) {
            $user->setConfirmationToken($this->tokenGenerator->generateToken());
        }

        $url = $this->router->generate('fos_user_profile_edit');
        $event->setResponse(new RedirectResponse($url));
    }

    public function onRegistrationCompleted(FilterUserResponseEvent $event)
    {
        $user = $event->getUser();
        $this->notificationHelper->enforceUnconfirmedEmailNotification($user);
        $this->mailer->sendConfirmationEmailMessage($user);
    }

    public function onEmailConfirmed(GetResponseUserEvent $event)
    {
        $event->getUser()->setEmailConfirmedAt(new \DateTime());
        $this->notificationHelper->clearUnconfirmedEmailNotification($event->getUser());
    }

}
