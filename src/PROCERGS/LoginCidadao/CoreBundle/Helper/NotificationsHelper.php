<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Helper;

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

    public function __construct(EntityManager $em, SecurityContext $context)
    {
        $this->em = $em;
        $this->context = $context;
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

}
