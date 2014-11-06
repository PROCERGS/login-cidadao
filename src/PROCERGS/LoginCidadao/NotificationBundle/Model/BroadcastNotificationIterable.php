<?php

namespace PROCERGS\LoginCidadao\NotificationBundle\Model;

use PROCERGS\LoginCidadao\NotificationBundle\Handler\AuthenticatedNotificationHandlerInterface;
use PROCERGS\LoginCidadao\NotificationBundle\Entity\Broadcast;

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
