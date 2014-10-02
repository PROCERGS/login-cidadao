<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Helper;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Notification\NotificationInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Doctrine\ORM\EntityManager;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Person;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Notification\Notification;
use PROCERGS\LoginCidadao\CoreBundle\Entity\InteractiveNotification;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Notification\Category;
use PROCERGS\LoginCidadao\CoreBundle\Exception\Notification\MissingCategoryException;

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

    /**
     * @var \Symfony\Component\Routing\Router
     */
    private $router;

    /**
     *
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    private $translator;
    private $container;
    private $unconfirmedEmailCategoryId;
    private $emptyPasswordCategoryId;

    public function __construct(EntityManager $em, SecurityContext $context, $container, $unconfirmedEmailCategoryId, $emptyPasswordCategoryId)
    {
        $this->em = $em;
        $this->context = $context;
        $this->container = $container;
        $this->router = $this->container->get('router');
        $this->translator = $this->container->get('translator');

        $this->unconfirmedEmailCategoryId = $unconfirmedEmailCategoryId;
        $this->emptyPasswordCategoryId = $emptyPasswordCategoryId;
    }

    private function getRepository()
    {
        return $this->em->getRepository("PROCERGSLoginCidadaoCoreBundle:Notification\Notification");
    }

    public function getUser()
    {
        return $this->context->getToken()->getUser();
    }

    public function getUnread($level = null)
    {
        return $this->getRepository()->findAllUnread($this->getUser(), $level);
    }
    
    public function getTotalUnread()
    {
        return $this->getRepository()->getTotalUnread($this->getUser());
    }

    public function send(NotificationInterface $notification)
    {
        if ($notification->canBeSent()) {
            $handler = $this->getNotificationHandler();
            $handler->post($notification);
        } else {
            $translator = $this->container->get('translator');
            throw new AccessDeniedException($translator->trans("This notification cannot be sent to this user. Check the notification level and whether the user has authorized the application."));
        }
    }

    protected function getDefaultNotification(Person $person, $title, $shortText, $text, $level, $icon, Category $category, $notification = null, $parameters = null)
    {
        $persisted = $this->getRepository()->findOneBy(array('person' => $person, 'category' => $category));
        if ($persisted instanceof NotificationInterface) {
            return $persisted;
        }

        if (is_null($notification)) {
            $notification = new Notification();
        }

        $text = strtr($text, $category->getPlaceholdersArray($parameters));
        $shortText = strtr($shortText, $category->getPlaceholdersArray($parameters));

        $notification->setPerson($person)
            ->setIcon($icon)
            ->setLevel($level)
            ->setTitle($title)
            ->setShortText($shortText)
            ->setText($text)
            ->setCategory($category);

        return $notification;
    }

    protected function getUnconfirmedEmailNotification(Person $person)
    {
        $title = $this->translator->trans(self::UNCONFIRMED_EMAIL_TITLE);
        $shortText = $this->translator->trans(self::UNCONFIRMED_EMAIL_SHORT_TEXT);
        $text = $this->translator->trans(self::UNCONFIRMED_EMAIL_FULL_TEXT);
        $level = NotificationInterface::LEVEL_EXTREME;
        $icon = 'glyphicon glyphicon-envelope';
        $url = $this->container->get('router')
            ->generate('lc_resend_confirmation_email');

        return $this->getDefaultNotification($person, $title, $shortText, $text, $level, $icon, $this->getUnconfirmedEmailCategory(), new Notification(), array('%url%' => $url));
    }

    protected function getEmptyPasswordNotification(Person $person)
    {
        $title = $this->translator->trans(self::EMPTY_PASSWORD_TITLE);
        $shortText = $this->translator->trans(self::EMPTY_PASSWORD_SHORT_TEXT);
        $text = $this->translator->trans(self::EMPTY_PASSWORD_FULL_TEXT);
        $level = NotificationInterface::LEVEL_IMPORTANT;
        $icon = 'glyphicon glyphicon-exclamation-sign';

        return $this->getDefaultNotification($person, $title, $shortText, $text, $level, $icon, $this->getEmptyPasswordCategory(), new Notification(), array());
    }

    public function clearUnconfirmedEmailNotification(Person $person)
    {
        $handler = $this->getNotificationHandler();
        $notification = $this->getUnconfirmedEmailNotification($person);
        if (!$notification->getCategory()) {
            $category = $this->getUnconfirmedEmailCategory();
            $notification->setCategory($category);
        }
        $notification->setRead(true);
        $handler->patch($notification, array());
    }

    /**
     * @deprecated since version 1.1.0
     * @param Person $person
     */
    public function enforceUnconfirmedEmailNotification(Person $person)
    {
        $category = $this->getUnconfirmedEmailCategory();
        $handler = $this->getNotificationHandler();

        $notification = $this->getUnconfirmedEmailNotification($person);
        if (!$notification->getCategory()) {
            $notification->setCategory($category);
        }
        $notification->setRead(false);
        $handler->patch($notification, array());
    }

    /**
     * @deprecated since version 1.1.0
     * @param Person $person
     */
    public function enforceEmptyPasswordNotification(Person $person)
    {
        $category = $this->getEmptyPasswordCategory();
        $handler = $this->getNotificationHandler();

        $notification = $this->getEmptyPasswordNotification($person);
        if (!$notification->getCategory()) {
            $notification->setCategory($category);
        }
        $notification->setRead(false);
        $handler->patch($notification, array());
    }

    /**
     * @deprecated since version 1.1.0
     * @param Person $person
     */
    public function clearEmptyPasswordNotification(Person $person)
    {
        $handler = $this->getNotificationHandler();
        $notification = $this->getEmptyPasswordNotification($person);
        $notification->setRead(true);
        $handler->patch($notification, array());
    }

    public function isUnconfirmedEmailNotification(NotificationInterface $notification)
    {
        return ($notification->getTitle() === self::UNCONFIRMED_EMAIL_TITLE);
    }

    private function getUnconfirmedEmailCategory()
    {
        $category = $this->em->getRepository('PROCERGSLoginCidadaoCoreBundle:Notification\Category')->find($this->unconfirmedEmailCategoryId);
        if (null === $category) {
            throw new MissingCategoryException("missing category for unconfirmed email, please configure your db");
        }
        return $category;
    }

    private function getEmptyPasswordCategory()
    {
        $category = $this->em->getRepository('PROCERGSLoginCidadaoCoreBundle:Notification\Category')->find($this->emptyPasswordCategoryId);
        if (null === $category) {
            throw new MissingCategoryException("missing category for empty password, please configure your db");
        }
        return $category;
    }

    /**
     *
     * @return \PROCERGS\LoginCidadao\CoreBundle\Handler\NotificationHandlerInterface
     */
    private function getNotificationHandler()
    {
        return $this->container->get('procergs.notification.handler');
    }

}
