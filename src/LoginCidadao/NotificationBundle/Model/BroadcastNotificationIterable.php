<?php

namespace LoginCidadao\NotificationBundle\Model;

use LoginCidadao\NotificationBundle\Handler\AuthenticatedNotificationHandlerInterface;
use LoginCidadao\NotificationBundle\Entity\Broadcast;

class BroadcastNotificationIterable extends NotificationIterable
{

    /** @var ClientInterface */
    protected $client;

    public function __construct(AuthenticatedNotificationHandlerInterface $handler,
                                $perIteration,
                                $offset = 0)
    {
        parent::__construct($handler, $perIteration, $offset);
    }

    protected function getCurrentData()
    {
        return $this->handler->allIdOffset($this->getPerIteration(),
                                           $this->getOffset(),
                                           $this->client);
    }

}
