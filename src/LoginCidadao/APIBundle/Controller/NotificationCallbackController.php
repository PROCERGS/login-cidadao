<?php

namespace LoginCidadao\APIBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations as REST;
use LoginCidadao\APIBundle\Security\Audit\Annotation as Audit;

class NotificationCallbackController extends BaseController
{

    /**
     * List all failed notification's callback.
     *
     * @ApiDoc(
     *   resource = true,
     *   output = {
     *     "groups" = {"public"}
     *   },
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @REST\View(
     *   templateVar="failedCallbacks"
     * )
     *
     * @param Request                $request       the request object
     * @param ParamFetcherInterface  $paramFetcher  param fetcher service
     *
     * @REST\Get("/notifications/callbacks/failed", name="api_1_get_failed_notification_callbacks")
     * @Audit\Loggable(type="SELECT")
     *
     * @return array
     */
    public function getFailedByClientAction()
    {

        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('LoginCidadaoNotificationBundle:FailedCallback');

        $failedCallbacks = $repo->findByClient($this->getClient());

        $context = $this->getSerializationContext(array("public", "public_profile"));
        return $this->renderWithContext($failedCallbacks, $context);
    }

}
