<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Helper;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use PROCERGS\LoginCidadao\CoreBundle\Entity\NotificationInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Doctrine\ORM\EntityManager;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Person;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Notification;

class NotificationsHelper
{

    const UNCONFIRMED_EMAIL_TITLE = 'notification.unconfirmed.email.title';
    const UNCONFIRMED_EMAIL_SHORT_TEXT = 'notification.unconfirmed.email.shortText';
    const UNCONFIRMED_EMAIL_FULL_TEXT = 'notification.unconfirmed.email.text';

    /**
     *
     * @var EntityManager
     */
    private $em;

    /**
     *
     * @var SecurityContext
     */
    private $context;
    private $container;

    public function __construct(EntityManager $em, SecurityContext $context,
                                $container)
    {
        $this->em = $em;
        $this->context = $context;
        $this->container = $container;
    }

    private function getRepository()
    {
        return $this->em->getRepository("PROCERGSLoginCidadaoCoreBundle:Notification");
    }

    public function getUser()
    {
        return $this->context->getToken()->getUser();
    }

    public function getUnreadExtreme()
    {
        return $this->getUnread(NotificationInterface::LEVEL_EXTREME);
    }

    public function getUnread($level = null)
    {
        return $this->getRepository()->findAllUnread($this->getUser(), $level);
    }

    public function send(NotificationInterface $notification)
    {
        if ($notification->canBeSent()) {
            $this->em->persist($notification);
            $this->em->flush();
        } else {
            $translator = $this->container->get('translator');
            throw new AccessDeniedException($translator->trans("This notification cannot be sent to this user. Check the notification level and whether the user has authorized the application."));
        }
    }

    protected function getUnconfirmedEmailNotification(Person $person)
    {
        $persisted = $this->getRepository()->findOneBy(array('person' => $person, 'title' => self::UNCONFIRMED_EMAIL_TITLE));
        if ($persisted instanceof NotificationInterface) {
            return $persisted;
        }

        $notification = new Notification();
        $notification->setPerson($person)
                ->setIcon('glyphicon glyphicon-envelope')
                ->setLevel(NotificationInterface::LEVEL_EXTREME)
                ->setTitle(self::UNCONFIRMED_EMAIL_TITLE)
                ->setShortText(self::UNCONFIRMED_EMAIL_SHORT_TEXT)
                ->setText(self::UNCONFIRMED_EMAIL_FULL_TEXT);

        return $notification;
    }

    public function clearUnconfirmedEmailNotification(Person $person)
    {
        $notification = $this->getUnconfirmedEmailNotification($person);
        $notification->setRead(true);
        $this->em->persist($notification);
        $this->em->flush();
    }

    public function enforceUnconfirmedEmailNotification(Person $person)
    {
        $notification = $this->getUnconfirmedEmailNotification($person);
        $notification->setRead(false);
        $this->em->persist($notification);
        $this->em->flush();
    }

}
