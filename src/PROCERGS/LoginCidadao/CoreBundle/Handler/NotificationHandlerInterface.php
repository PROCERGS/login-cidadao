<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Handler;

use PROCERGS\LoginCidadao\CoreBundle\Entity\Notification\NotificationInterface;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Person;
use FOS\OAuthServerBundle\Model\ClientInterface;

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
     * @param Person           $person  the user to get notifications from
     * @param int              $limit   the limit of the result
     * @param int              $offset  starting from the offset
     * @param int              $orderby the ordering criteria
     *
     * @return array
     */
    public function getAllFromPerson(Person $person, $limit = 5, $offset = 0,
                                     $orderby = null);

    /**
     * Get a list of an user's Notifications restricted by Client.
     *
     * @param Person           $person  the user to get notifications from
     * @param ClientInterface  $client  the requesting Client
     * @param int              $limit   the limit of the result
     * @param int              $offset  starting from the offset
     * @param int              $orderby the ordering criteria
     *
     * @return array
     */
    public function getAllFromPersonByClient(Person $person,
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
}
