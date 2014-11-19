<?php

namespace PROCERGS\LoginCidadao\NotificationBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use PROCERGS\LoginCidadao\NotificationBundle\Model\NotificationInterface;

class NotificationEvent extends Event
{

    /** @var NotificationInterface */
    protected $notification;

    public function __construct(NotificationInterface $notification)
    {
        $this->notification = $notification;
    }

    /**
     *
     * @return NotificationInterface
     */
    public function getNotification()
    {
        return $this->notification;
    }

}
