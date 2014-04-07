<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Helper;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use PROCERGS\LoginCidadao\CoreBundle\Entity\NotificationInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Doctrine\ORM\EntityManager;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Person;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Notification;
use PROCERGS\LoginCidadao\CoreBundle\Entity\InteractiveNotification;

class NotificationsHelper
{

    const UNCONFIRMED_EMAIL_TITLE = 'notification.unconfirmed.email.title';
    const UNCONFIRMED_EMAIL_SHORT_TEXT = 'notification.unconfirmed.email.shortText';
    const UNCONFIRMED_EMAIL_FULL_TEXT = 'notification.unconfirmed.email.text';
    const EMPTY_PASSWORD_TITLE = 'notification.empty.password.title';
    const EMPTY_PASSWORD_SHORT_TEXT = 'notification.empty.password.shortText';
    const EMPTY_PASSWORD_FULL_TEXT = 'notification.empty.password.text';

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

    public function getUnreadExcludeExtreme()
    {
        return $this->getRepository()->findUnreadUpToLevel($this->getUser(),
                        NotificationInterface::LEVEL_IMPORTANT);
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

    protected function getDefaultNotification(Person $person, $title,
                                              $shortText, $text, $level, $icon,
                                              $notification = null)
    {
        $persisted = $this->getRepository()->findOneBy(array('person' => $person, 'title' => $title));
        if ($persisted instanceof NotificationInterface) {
            return $persisted;
        }

        if (is_null($notification)) {
            $notification = new Notification();
        }
        $notification->setPerson($person)
                ->setIcon($icon)
                ->setLevel($level)
                ->setTitle($title)
                ->setShortText($shortText)
                ->setText($text);

        return $notification;
    }

    protected function getUnconfirmedEmailNotification(Person $person)
    {
        $title = self::UNCONFIRMED_EMAIL_TITLE;
        $shortText = self::UNCONFIRMED_EMAIL_SHORT_TEXT;
        $text = self::UNCONFIRMED_EMAIL_FULL_TEXT;
        $level = NotificationInterface::LEVEL_EXTREME;
        $icon = 'glyphicon glyphicon-envelope';

        return $this->getDefaultNotification($person, $title, $shortText, $text,
                        $level, $icon, new InteractiveNotification());
    }

    protected function getEmptyPasswordNotification(Person $person)
    {
        $title = self::EMPTY_PASSWORD_TITLE;
        $shortText = self::EMPTY_PASSWORD_SHORT_TEXT;
        $text = self::EMPTY_PASSWORD_FULL_TEXT;
        $level = NotificationInterface::LEVEL_IMPORTANT;
        $icon = 'glyphicon glyphicon-exclamation-sign';

        return $this->getDefaultNotification($person, $title, $shortText, $text,
                        $level, $icon, new InteractiveNotification());
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
        $notification->setTarget('lc_resend_confirmation_email');
        $this->em->persist($notification);
        $this->em->flush();
    }

    public function enforceEmptyPasswordNotification(Person $person)
    {
        $notification = $this->getEmptyPasswordNotification($person);
        $notification->setRead(false);
        $notification->setTarget('fos_user_change_password');
        $this->em->persist($notification);
        $this->em->flush();
    }

    public function clearEmptyPasswordNotification(Person $person)
    {
        $notification = $this->getEmptyPasswordNotification($person);
        $notification->setRead(true);
        $this->em->persist($notification);
        $this->em->flush();
    }

    public function isUnconfirmedEmailNotification(NotificationInterface $notification)
    {
        return ($notification->getTitle() === self::UNCONFIRMED_EMAIL_TITLE);
    }

}
