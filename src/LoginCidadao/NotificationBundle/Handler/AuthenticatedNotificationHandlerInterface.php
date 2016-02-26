<?php

namespace LoginCidadao\NotificationBundle\Handler;

use LoginCidadao\NotificationBundle\Model\NotificationInterface;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use LoginCidadao\NotificationBundle\Model\CategoryInterface;
use LoginCidadao\NotificationBundle\Model\NotificationSettings;

interface AuthenticatedNotificationHandlerInterface
{

    /**
     * Get a Notification given the id
     *
     * @api
     * @param mixed $id
     * @return NotificationInterface
     */
    public function get($id);

    /**
     * Get a list of Notifications.
     *
     * @param int $limit   the limit of the result
     * @param int $offset  starting from the offset
     * @param int|null $orderby the ordering criteria
     *
     * @return array
     */
    public function all($limit = 5, $offset = 0, $orderby = null);

    /**
     * Get a list of Notifications offseting by Id.
     *
     * @param int $limit   the limit of the result
     * @param int $offset  starting from the id
     * @param ClientInterface $client optionally filters by OAuth Client
     *
     * @return array
     */
    public function allIdOffset(
        $limit = 5,
        $offset = 0,
        ClientInterface $client = null
    );

    /**
     * Get a list of an user's Notifications restricted by Client.
     *
     * @param ClientInterface  $client  the requesting Client
     * @param int              $limit   the limit of the result
     * @param int              $offset  starting from the offset
     * @param int|null         $orderby the ordering criteria
     *
     * @return array
     */
    public function getAllFromClient(
        ClientInterface $client,
        $limit = 5,
        $offset = 0,
        $orderby = null
    );

    /**
     * Retrieves a person's settings.
     *
     * @param null|CategoryInterface $client optionally filter by category
     * @param null|ClientInterface $client optionally filter by client
     * @return PersonNotificationOption[]
     */
    public function getSettings(
        CategoryInterface $category = null,
        ClientInterface $client = null
    );

    /**
     * Ensures that the Person has all it's notifications setup.
     *
     * @param ClientInterface $client
     */
    public function initializeSettings(ClientInterface $client);

    /**
     * Mark the specified range of IDs as read.
     *
     * @param int $start
     * @param int $end
     */
    public function markRangeAsRead($start, $end);

    /**
     *
     * @param ClientInterface|null $client filter by client
     * @param CategoryInterface|null $category filter by category
     * @return NotificationSettings PersonNotificationOptions grouped by Client
     */
    public function getGroupedSettings(
        ClientInterface $client = null,
        CategoryInterface $category = null
    );

    /**
     * Return a person's number of unread notifications.
     */
    public function countUnread();

    /**
     * Return a person's number of unread notifications grouping by Client.
     */
    public function countUnreadByClient();
}
