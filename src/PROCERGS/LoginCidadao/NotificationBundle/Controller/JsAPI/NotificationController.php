<?php

namespace PROCERGS\LoginCidadao\NotificationBundle\Controller\JsAPI;

use FOS\RestBundle\Util\Codes;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations as REST;
use FOS\RestBundle\Controller\FOSRestController;
use PROCERGS\LoginCidadao\NotificationBundle\Handler\NotificationHandlerInterface;

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
     * @REST\Put("/notifications/{start}/{end}/read", requirements={"start" = "\d+","end" = "\d+"}, name="js_api_1_put_notification_read")
     */
    public function putReadAction($start, $end)
    {
        $handler = $this->getNotificationHandler();
        $person = $this->getUser();

        return $handler->markRangeAsRead($person, $start, $end);
    }

    /**
     * @return NotificationHandlerInterface
     */
    private function getNotificationHandler()
    {
        return $this->get('procergs.notification.handler');
    }

}
