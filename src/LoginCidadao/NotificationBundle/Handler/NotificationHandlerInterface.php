<?php

namespace LoginCidadao\NotificationBundle\Handler;

use LoginCidadao\NotificationBundle\Model\NotificationInterface;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use LoginCidadao\NotificationBundle\Model\CategoryInterface;
use LoginCidadao\NotificationBundle\Model\NotificationSettings;

interface NotificationHandlerInterface
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
     * @param int $orderby the ordering criteria
     *
     * @return NotificationInterface[]
     */
    public function all($limit = 5, $offset = 0, $orderby = null);

    /**
     * Get a list of an user's Notifications.
     *
     * @param PersonInterface  $person  the user to get notifications from
     * @param int              $limit   the limit of the result
     * @param int              $offset  starting from the offset
     * @param int              $orderby the ordering criteria
     *
     * @return NotificationInterface[]
     */
    public function getAllFromPerson(PersonInterface $person, $limit = 5,
                                        $offset = 0, $orderby = null);

    /**
     * Same as getAllFromPerson but uses the notification's id as the offset.
     *
     * @param PersonInterface $person
     * @param type $limit
     * @param type $offset the id of the last notification received
     * @param ClientInterface $client optionally filters by OAuth Client
     *
     * @return NotificationInterface[]
     */
    public function getAllFromPersonIdOffset(PersonInterface $person,
                                                $limit = 5, $offset = 0,
                                                ClientInterface $client = null);

    /**
     * Get a list of an user's Notifications restricted by Client.
     *
     * @param PersonInterface  $person  the user to get notifications from
     * @param ClientInterface  $client  the requesting Client
     * @param int              $limit   the limit of the result
     * @param int              $offset  starting from the offset
     * @param int              $orderby the ordering criteria
     *
     * @return NotificationInterface[]
     */
    public function getAllFromPersonByClient(PersonInterface $person,
                                                ClientInterface $client,
                                                $limit = 5, $offset = 0,
                                                $orderby = null);

    /**
     * Post Notification, creates a new Notification
     *
     * @api
     *
     * @param array $parameters
     *
     * @return NotificationInterface
     */
    public function post(array $parameters);

    /**
     * Edit a Notification.
     *
     * @api
     *
     * @param NotificationInterface $notification
     * @param array                 $parameters
     *
     * @return NotificationInterface
     */
    public function put(NotificationInterface $notification, array $parameters);

    /**
     * Partially update a Notification.
     *
     * @api
     *
     * @param NotificationInterface $notification
     * @param array                 $parameters
     *
     * @return NotificationInterface
     */
    public function patch(NotificationInterface $notification, array $parameters);

    /**
     * Retrieves a person's settings.
     *
     * @param PersonInterface   $person
     * @param CategoryInterface $client optionally filter by category
     * @param ClientInterface $client optionally filter by client
     *
     * @return PersonNotificationOption[]
     */
    public function getSettings(PersonInterface $person,
                                CategoryInterface $category = null,
                                ClientInterface $client = null);

    /**
     * Retrieves a person's settings for a specific Client.
     *
     * @param PersonInterface $person
     * @param ClientInterface $client optionally filters by category.
     *
     * @return PersonNotificationOption[]
     */
    public function getSettingsByClient(PersonInterface $person,
                                        ClientInterface $client);

    /**
     * Ensures that the given Person has all it's notifications setup.
     *
     * @param PersonInterface $person
     * @param ClientInterface $client
     *
     * @return boolean
     */
    public function initializeSettings(PersonInterface $person,
                                        ClientInterface $client);

    /**
     * Mark the specified range of IDs as read.
     *
     * @param PersonInterface $person
     * @param int $start
     * @param int $end
     *
     * @return array
     */
    public function markRangeAsRead(PersonInterface $person, $start, $end);

    /**
     *
     * @param PersonInterface $person
     * @param ClientInterface $client filter by client
     * @param CategoryInterface $category filter by category
     * @return NotificationSettings PersonNotificationOptions grouped by Client
     */
    public function getGroupedSettings(PersonInterface $person,
                                        ClientInterface $client = null,
                                        CategoryInterface $category = null);

    /**
     *
     * @param PersonInterface $person
     * @return AuthenticatedNotificationHandlerInterface Returns an AuthenticatedNotificationHandler
     */
    public function getAuthenticatedHandler(PersonInterface $person);

    /**
     * Return a person's number of unread notifications.
     * @param PersonInterface $person
     *
     * @return integer
     */
    public function countUnread(PersonInterface $person);

    /**
     * Return a person's number of unread notifications grouping by Client
     * @param PersonInterface $person
     * @param ClientInterface $client
     *
     * @return array
     */
    public function countUnreadByClient(PersonInterface $person);

    /**
     * Renders the
     * @param NotificationInterface $notification
     *
     * @return string
     */
    public function getEmailHtml(NotificationInterface $notification);
    
    /**
     * Get default OAUTH client
     */
    public function getLoginCidadaoClient();
    /**
     * Retrieves a person's unread notifications
     * @param PersonInterface $person
     * @param int $limit
     */
    public function getUnread(PersonInterface $person, $limit);
}
