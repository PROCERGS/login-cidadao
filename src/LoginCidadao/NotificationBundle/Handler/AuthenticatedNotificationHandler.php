<?php

namespace LoginCidadao\NotificationBundle\Handler;

use LoginCidadao\NotificationBundle\Handler\NotificationHandlerInterface;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use LoginCidadao\NotificationBundle\Model\CategoryInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AuthenticatedNotificationHandler implements AuthenticatedNotificationHandlerInterface
{

    /** @var PersonInterface */
    private $person;

    /** @var NotificationHandlerInterface */
    private $handler;

    public function __construct(PersonInterface $person,
                                NotificationHandlerInterface $handler)
    {
        $this->person = $person;
        $this->handler = $handler;
    }

    public function all($limit = 5, $offset = 0, $orderby = null)
    {
        return $this->handler->getAllFromPerson($this->person, $limit, $offset,
                                                $orderby);
    }

    public function allIdOffset($limit = 5, $offset = 0,
                                ClientInterface $client = null)
    {
        return $this->handler->getAllFromPersonIdOffset($this->person, $limit,
                                                        $offset, $client);
    }

    public function getAllFromClient(ClientInterface $client, $limit = 5,
                                        $offset = 0, $orderby = null)
    {
        return $this->handler->getAllFromPersonByClient($this->person, $client,
                                                        $limit, $offset,
                                                        $orderby);
    }

    public function get($id)
    {
        $notification = $this->handler->get($id);
        if ($notification->getPerson()->getId() !== $this->person->getId()) {
            throw new AccessDeniedHttpException();
        }
        return $this->handler->get($id);
    }

    public function getSettings(CategoryInterface $category = null,
                                ClientInterface $client = null)
    {
        return $this->handler->getSettings($this->person, $category, $client);
    }

    public function initializeSettings(ClientInterface $client = null)
    {
        return $this->handler->initializeSettings($this->person, $client);
    }

    public function markRangeAsRead($start, $end)
    {
        return $this->handler->markRangeAsRead($this->person, $start, $end);
    }

    public function getGroupedSettings(ClientInterface $client = null,
                                        CategoryInterface $category = null)
    {
        return $this->handler->getGroupedSettings($this->person, $client,
                                                    $category);
    }

    public function countUnread()
    {
        return $this->handler->countUnread($this->person);
    }

    public function countUnreadByClient()
    {
        return $this->handler->countUnreadByClient($this->person);
    }

}
