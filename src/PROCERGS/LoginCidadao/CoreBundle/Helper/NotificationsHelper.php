<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Helper;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use PROCERGS\LoginCidadao\CoreBundle\Entity\NotificationInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Doctrine\ORM\EntityManager;

class NotificationsHelper
{

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

    public function __construct(EntityManager $em, SecurityContext $context, $container)
    {
        $this->em = $em;
        $this->context = $context;
        $this->container = $container;
    }

    public function getUser()
    {
        return $this->context->getToken()->getUser();
    }

    public function getUnread()
    {
        return $this->em->getRepository("PROCERGSLoginCidadaoCoreBundle:Notification")
                        ->findAllUnread($this->getUser());
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
}
