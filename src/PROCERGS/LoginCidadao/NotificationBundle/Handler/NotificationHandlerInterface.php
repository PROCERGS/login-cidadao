<?php

namespace PROCERGS\LoginCidadao\NotificationBundle\Handler;

use PROCERGS\LoginCidadao\NotificationBundle\Entity\NotificationInterface;
use PROCERGS\LoginCidadao\CoreBundle\Model\PersonInterface;
use PROCERGS\OAuthBundle\Model\ClientInterface;
use PROCERGS\LoginCidadao\NotificationBundle\Model\CategoryInterface;

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
     * @return array
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
     * @return array
     */
    public function getAllFromPerson(PersonInterface $person, $limit = 5,
                                     $offset = 0, $orderby = null);

    /**
     * Get a list of an user's Notifications restricted by Client.
     *
     * @param PersonInterface  $person  the user to get notifications from
     * @param ClientInterface  $client  the requesting Client
     * @param int              $limit   the limit of the result
     * @param int              $offset  starting from the offset
     * @param int              $orderby the ordering criteria
     *
     * @return array
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
     * @param CategoryInterface $client optionally filters by category.
     */
    public function getSettings(PersonInterface $person,
                                CategoryInterface $category = null);

    /**
     * Retrieves a person's settings for a specific Client.
     *
     * @param PersonInterface $person
     * @param ClientInterface $client optionally filters by category.
     */
    public function getSettingsByClient(PersonInterface $person,
                                        ClientInterface $client);

    /**
     * Ensures that the given Person has all it's notifications setup.
     *
     * @param PersonInterface $person
     * @param ClientInterface $client
     */
    public function initializeSettings(PersonInterface $person,
                                       ClientInterface $client);

    /**
     * Mark the specified range of IDs as read.
     *
     * @param PersonInterface $person
     * @param int $start
     * @param int $end
     */
    public function markRangeAsRead(PersonInterface $person, $start, $end);
}
