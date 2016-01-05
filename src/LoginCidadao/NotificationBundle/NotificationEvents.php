<?php

namespace LoginCidadao\NotificationBundle;

final class NotificationEvents
{

    /**
     * The notification event is thrown each time a notification is created.
     *
     * The Event Listener receives an
     * LoginCidadao\NotificationBundle\Event\NotificationEvent instance
     *
     * @var string
     */
    const NOTIFICATION_INITIALIZE = 'notification.initialize';

    /**
     * The notification event is thrown each time a notification is saved.
     *
     * The Event Listener receives an
     * LoginCidadao\NotificationBundle\Event\NotificationEvent instance
     *
     * @var string
     */
    const NOTIFICATION_SUCCESS = 'notification.success';

    /**
     * The notification event is thrown each time a notification sending proccess
     * is finished.
     *
     * The Event Listener receives an
     * LoginCidadao\NotificationBundle\Event\NotificationEvent instance
     *
     * @var string
     */
    const NOTIFICATION_COMPLETED = 'notification.completed';

}
