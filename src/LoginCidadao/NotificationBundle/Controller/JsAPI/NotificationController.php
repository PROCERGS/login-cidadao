<?php

namespace LoginCidadao\NotificationBundle\Controller\JsAPI;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations as REST;
use FOS\RestBundle\Controller\FOSRestController;
use LoginCidadao\NotificationBundle\Handler\NotificationHandlerInterface;

/**
 * @REST\Prefix("/")
 */
class NotificationController extends FOSRestController
{

    /**
     * Mark a range of notifications (IDs) as read.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Mark a range of notifications (IDs) as read.",
     *   output = "array",
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     * @REST\View(templateVar="result")
     * @param int     $start
     * @param int     $end
     * @return array
     * @REST\Put("/notifications/{start}/{end}/read", requirements={"start" = "\d+","end" = "\d+"}, name="js_api_1_put_notification_read_range")
     */
    public function putReadRangeAction($start, $end)
    {
        $person = $this->getUser();
        $handler = $this->getNotificationHandler()
            ->getAuthenticatedHandler($person);

        return $handler->markRangeAsRead($start, $end);
    }

    /**
     * Get and read a notification
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Get and read a notification",
     *   output = "html",
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     * @REST\View(templateVar="html")
     * @return string
     * @REST\Get("/notifications/{id}/html", name="js_api_1_get_notification_html")
     */
    public function getNotificationHtmlAction($id)
    {
        $person = $this->getUser();
        $handler = $this->getNotificationHandler()
            ->getAuthenticatedHandler($person);

        $notification = $handler->get($id);

        if ($notification->isRead() === false) {
            $this->putReadAction($notification->getId());
        }

        return $notification->getHtmlTemplate();
    }

    /**
     * Mark a notification as read.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Mark a notification as read.",
     *   output = "array",
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     * @REST\View(templateVar="result")
     * @param int     $notificationId
     * @return array
     * @REST\Put("/notifications/{notificationId}/read", requirements={"notificationId" = "\d+"}, name="js_api_1_put_notification_read")
     */
    public function putReadAction($notificationId)
    {
        return $this->putReadRangeAction($notificationId, $notificationId);
    }

    /**
     * Get the unread notifications count.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Get the unread notifications count.",
     *   output = "array",
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     * @REST\View(templateVar="result")
     * @return array
     * @REST\Get("/notifications/count/unread", name="js_api_1_get_notification_count_unread")
     */
    public function getUnreadCountAction()
    {
        $person = $this->getUser();
        $result = $this->getNotificationHandler()->countUnreadByClient($person);
        $result['total'] = 0;
        if (!empty($result)) {
            foreach ($result as $client) {
                $result['total'] += $client['total'];
            }
        }

        return $result;
    }

    /**
     * @return NotificationHandlerInterface
     */
    private function getNotificationHandler()
    {
        return $this->get('lc.notification.handler');
    }

}
