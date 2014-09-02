<?php

namespace PROCERGS\LoginCidadao\APIBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Util\Codes;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations as REST;

class NotificationController extends FOSRestController
{

    /**
     * List all notifications for a given Person.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @REST\QueryParam(name="personId", requirements="\d+", nullable=false, description="The person's id")
     * @REST\QueryParam(name="offset", requirements="\d+", nullable=true, description="Offset from which to start listing Notifications")
     * @REST\QueryParam(name="limit", requirements="\d+", default="5", description="How many notifications to return.")
     * @REST\View(
     *   templateVar="notifications"
     * )
     *
     * @param Request                $request       the request object
     * @param ParamFetcherInterface  $paramFetcher  param fetcher service
     *
     * @return array
     * @REST\Get("/people/{personId}/notifications")
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
