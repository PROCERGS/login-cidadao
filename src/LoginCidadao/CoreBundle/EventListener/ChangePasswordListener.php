<?php

namespace LoginCidadao\CoreBundle\EventListener;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use FOS\UserBundle\Event\FilterUserResponseEvent;

class ChangePasswordListener implements EventSubscriberInterface
{
    /** @var UrlGeneratorInterface */
    private $router;

    /** @var SessionInterface */
    private $session;

    /** @var string */
    private $defaultPasswordEncoder;

    public function __construct(UrlGeneratorInterface $router,
                                SessionInterface $session,
                                $defaultPasswordEncoder)
    {
        $this->router                 = $router;
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
        );
    }

    public function onChangePasswordSuccess(FormEvent $event)
    {
        $this->setPasswordEncoderName($event);
        $this->redirectToPasswordChangePage($event);
    }

    private function redirectToPasswordChangePage(FormEvent $event)
    {
        $url = $this->router->generate('fos_user_change_password');
        $event->setResponse(new RedirectResponse($url));
    }

    public function setPasswordEncoderName(FormEvent $event)
    {
        $person = $event->getForm()->getData();
        $person->setPasswordEncoderName($this->defaultPasswordEncoder);
        $this->session->remove('force_password_change');
    }
}
