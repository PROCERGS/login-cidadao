<?php

namespace LoginCidadao\NotificationBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use LoginCidadao\NotificationBundle\Model\NotificationInterface;

class NotificationEvent extends Event
{

    /** @var NotificationInterface */
    protected $notification;

    /** @var array */
    protected $placeholders = null;

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

    /**
     * @return array
     */
    public function getPlaceholders()
    {
        return $this->placeholders;
    }

    /**
     * @param array $placeholders
     * @return NotificationEvent
     */
    public function setPlaceholders(array $placeholders = null)
    {
        $this->placeholders = $placeholders;
        return $this;
    }

}
