<?php

namespace PROCERGS\LoginCidadao\NotificationBundle\Helper;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use PROCERGS\LoginCidadao\NotificationBundle\Model\NotificationInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Doctrine\ORM\EntityManager;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Person;
use PROCERGS\LoginCidadao\NotificationBundle\Entity\Notification;
use PROCERGS\LoginCidadao\NotificationBundle\Entity\Category;
use PROCERGS\LoginCidadao\NotificationBundle\Exception\MissingCategoryException;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use PROCERGS\LoginCidadao\NotificationBundle\Form\NotificationType;
use JMS\Serializer\SerializationContext;
use PROCERGS\LoginCidadao\NotificationBundle\Handler\NotificationHandler;
use PROCERGS\LoginCidadao\NotificationBundle\Entity\Placeholder;

class NotificationsHelper
{
    const EMPTY_PASSWORD_TITLE      = 'notification.empty.password.title';
    const EMPTY_PASSWORD_SHORT_TEXT = 'notification.empty.password.shortText';
    const EMPTY_PASSWORD_FULL_TEXT  = 'notification.empty.password.text';
    const EMPTY_PASSWORD_CLICK      = 'notification.empty.password.click.here';

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

    public function __construct(EntityManager $em, SecurityContext $context,
                                $container, $unconfirmedEmailCategoryId,
                                $emptyPasswordCategoryId)
    {
        $this->em                         = $em;
        $this->context                    = $context;
        $this->container                  = $container;
        $this->router                     = $this->container->get('router');
        $this->translator                 = $this->container->get('translator');
        $this->unconfirmedEmailCategoryId = $unconfirmedEmailCategoryId;
        $this->emptyPasswordCategoryId    = $emptyPasswordCategoryId;
    }

    private function getRepository()
    {
        return $this->em->getRepository("PROCERGSLoginCidadaoNotificationBundle:Notification");
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
        return $this->getNotificationHandler()->countUnread($this->getUser());
    }

    public function send(NotificationInterface $notification)
    {
        if ($notification->canBeSent()) {
            $handler = $this->getNotificationHandler();

            $serializer = $this->container->get('jms_serializer');
            $context    = SerializationContext::create()->setGroups('form');
            $array      = json_decode($serializer->serialize($notification,
                    'json', $context), true);

            $handler->patch($notification, array());
        } else {
            $translator = $this->container->get('translator');
            throw new AccessDeniedException($translator->trans("This notification cannot be sent to this user. Check the notification level and whether the user has authorized the application."));
        }
    }

    private function getDefaultNotification(Person $person, $title, $shortText,
                                            $text, $icon, Category $category,
                                            $notification, $parameters = null)
    {
        $notification->setPerson($person)
            ->setIcon($icon)
            ->setTitle($title)
            ->setShortText($shortText)
            ->setCategory($category)
            ->setSender($category->getClient())
            ->setPlaceholders($parameters);

        return $notification;
    }

    /**
     * @deprecated since version 1.1.0
     * @param Person $person
     */
    public function enforceEmptyPasswordNotification(Person $person)
    {
        $handler = $this->getNotificationHandler();

        $title        = $this->translator->trans(self::EMPTY_PASSWORD_TITLE);
        $shortText    = $this->translator->trans(self::EMPTY_PASSWORD_SHORT_TEXT);
        $text         = $this->translator->trans(self::EMPTY_PASSWORD_FULL_TEXT);
        $icon         = 'glyphicon glyphicon-exclamation-sign';
        $url          = $this->container->get('router')
            ->generate('fos_user_change_password', array(), true);
        $notification = $this->getDefaultNotification($person, $title,
            $shortText, $text, $icon, $this->getEmptyPasswordCategory(),
            new Notification(),
            array(new Placeholder('link', $url), new Placeholder('linktitle',
                $title), new Placeholder('linkclick',
                $this->translator->trans(self::EMPTY_PASSWORD_CLICK))));
        return $handler->patch($notification, array());
    }

    /**
     * @deprecated since version 1.1.0
     * @param Person $person
     */
    public function clearEmptyPasswordNotification(Person $person)
    {

    }

    private function getEmptyPasswordCategory()
    {
        $category = $this->em->getRepository('PROCERGSLoginCidadaoNotificationBundle:Category')->findOneByUid($this->emptyPasswordCategoryId);
        if (null === $category) {
            throw new MissingCategoryException("Missing category id for empty password, please edit your parameters.yml");
        }
        return $category;
    }

    /**
     *
     * @return \PROCERGS\LoginCidadao\NotificationBundle\Handler\NotificationHandlerInterface
     */
    private function getNotificationHandler()
    {
        return $this->container->get('procergs.notification.handler');
    }

    public function revokedCpfNotification(Person $person)
    {
        $category     = $this->getEmptyPasswordCategory();
        $handler      = $this->getNotificationHandler();
        $title        = $this->translator->trans('notification.nfg.revoked.cpf.title');
        $shortText    = $this->translator->trans('notification.nfg.revoked.cpf.message');
        $text         = $shortText;
        $icon         = 'glyphicon glyphicon-exclamation-sign';
        $notification = $this->getDefaultNotification($person, $title,
            $shortText, $text, $icon, $category, new Notification(),
            array(new Placeholder('link', ''), new Placeholder('linktitle', ''),
            new Placeholder('linkclick', '')));
        return $handler->patch($notification, array());
    }

    public function overwriteCpfNotification(Person $person)
    {
        $category     = $this->getEmptyPasswordCategory();
        $handler      = $this->getNotificationHandler();
        $title        = $this->translator->trans('notification.nfg.overwrite.cpf.title');
        $shortText    = $this->translator->trans('notification.nfg.overwrite.cpf.message');
        $text         = $shortText;
        $icon         = 'glyphicon glyphicon-exclamation-sign';
        $notification = $this->getDefaultNotification($person, $title,
            $shortText, $text, $icon, $category, new Notification(),
            array(new Placeholder('link', ''), new Placeholder('linktitle', ''),
            new Placeholder('linkclick', '')));
        return $handler->patch($notification, array());
    }
}