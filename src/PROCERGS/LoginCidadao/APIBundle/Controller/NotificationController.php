<?php

namespace PROCERGS\LoginCidadao\APIBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\Controller\Annotations;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class NotificationController extends FOSRestController
{

    /**
     * List all notifications.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Annotations\QueryParam(name="offset", requirements="\d+", nullable=true, description="Offset from which to start listing Notifications")
     * @Annotations\QueryParam(name="limit", requirements="\d+", default="5", description="How many notifications to return.")
     * @Annotations\View(
     *   templateVar="notifications"
     * )
     *
     * @param Request                $request       the request object
     * @param ParamFetcherInterface  $paramFetcher  param fetcher service
     *
     * @return array
     */
    public function getNotificationsAction(Request $request,
                                           ParamFetcherInterface $paramFetcher)
    {
        $offset = $paramFetcher->get('offset');
        $offset = null == $offset ? 0 : $offset;
        $limit = $paramFetcher->get('limit');

        return $this->getNotificationHandler()->all($limit, $offset);
    }

    /**
     *
     * @return \PROCERGS\LoginCidadao\CoreBundle\Handler\NotificationHandlerInterface
     */
    private function getNotificationHandler()
    {
        return $this->container->get('procergs.notification.handler');
    }

}
