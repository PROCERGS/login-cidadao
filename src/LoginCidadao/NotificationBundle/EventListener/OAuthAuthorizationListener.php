<?php

namespace LoginCidadao\NotificationBundle\EventListener;

use FOS\OAuthServerBundle\Event\OAuthEvent;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\NotificationBundle\Handler\NotificationHandlerInterface;
use Doctrine\ORM\EntityManager;

class OAuthAuthorizationListener
{

    /** @var \LoginCidadao\CoreBundle\Entity\PersonRepository */
    private $personRepo;

    /** @var NotificationHandlerInterface */
    private $notificationHandler;

    public function __construct(EntityManager $em,
                                NotificationHandlerInterface $notificationHandler)
    {
        $this->personRepo = $em->getRepository('LoginCidadaoCoreBundle:Person');
        $this->notificationHandler = $notificationHandler;
    }

    public function onPostAuthorizationProcess(OAuthEvent $event)
    {
        if ($event->isAuthorizedClient()) {
            if (null !== $client = $event->getClient()) {
                $this->setupNotificationSettings($event);
            }
        }
    }

    /**
     * @param OAuthEvent $event
     * @return PersonInterface
     */
    protected function getUser(OAuthEvent $event)
    {
        $username = $event->getUser()->getUsername();
        return $this->personRepo->findOneBy(compact('username'));
    }

    /**
     * We setup the notification settings regardless of the client asking the
     * scope or not.
     * @param OAuthEvent $event
     */
    public function setupNotificationSettings(OAuthEvent $event)
    {
        $person = $this->getUser($event);
        $client = $event->getClient();
        $this->notificationHandler->getSettingsByClient($person, $client);
        $this->notificationHandler->initializeSettings($person, $client);
    }

}
