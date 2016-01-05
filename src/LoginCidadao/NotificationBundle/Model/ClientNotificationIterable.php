<?php

namespace LoginCidadao\NotificationBundle\Model;

use LoginCidadao\NotificationBundle\Handler\AuthenticatedNotificationHandlerInterface;
use LoginCidadao\OAuthBundle\Model\ClientInterface;

class ClientNotificationIterable extends NotificationIterable
{

    /** @var ClientInterface */
    protected $client;

    public function __construct(AuthenticatedNotificationHandlerInterface $handler,
                                ClientInterface $client, $perIteration,
                                $offset = 0)
    {
        $this->client = $client;
        parent::__construct($handler, $perIteration, $offset);
    }

    protected function getCurrentData()
    {
        return $this->handler->allIdOffset($this->getPerIteration(),
                                           $this->getOffset(),
                                           $this->client);
    }

}
