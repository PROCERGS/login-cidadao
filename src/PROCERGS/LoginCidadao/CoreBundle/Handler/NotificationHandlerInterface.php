<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Handler;

use PROCERGS\LoginCidadao\CoreBundle\Entity\Notification\NotificationInterface;

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
     * @param int $limit  the limit of the result
     * @param int $offset starting from the offset
     *
     * @return array
     */
    public function all($limit = 5, $offset = 0);

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
