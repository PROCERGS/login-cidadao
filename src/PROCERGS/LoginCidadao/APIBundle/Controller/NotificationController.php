<?php

namespace PROCERGS\LoginCidadao\APIBundle\Controller;

use FOS\RestBundle\Util\Codes;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations as REST;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @REST\Prefix("")
 */
class NotificationController extends BaseController
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
     * @REST\Get("/notifications")
     */
    public function getNotificationsAction(Request $request,
                                           ParamFetcherInterface $paramFetcher)
    {
        $offset = $paramFetcher->get('offset');
        $offset = null == $offset ? 0 : $offset;
        $limit = $paramFetcher->get('limit');

        $person = $this->getUser();
        $client = $this->getClient();
        $scopes = $this->getClientScope($person);

        if (array_search('get_all_notifications', $scopes) === false) {
            $notifications = $this->getNotificationHandler()->getAllFromPersonByClient($person,
                    $client, $limit, $offset);
        } else {
            $notifications = $this->getNotificationHandler()->getAllFromPerson($person,
                    $limit, $offset);
        }

        return $this->renderWithContext($notifications);
    }

    /**
     * Get single Notification.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Gets a Notification for a given id",
     *   output = "PROCERGS\NotificationServiceBundle\Entity\Notification",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     403 = "Returned when trying to access another user's Notification",
     *     404 = "Returned when the notification is not found"
     *   }
     * )
     * @REST\View(templateVar="notification")
     * @param Request $request the request object
     * @param int     $id
     * @return array
     * @throws NotFoundHttpException when notification not exist
     * @throws AccessDeniedHttpException when trying to access another user's Notification
     * @REST\Get("/notifications/{id}", name="api_1_get_notification")
     */
    public function getNotificationAction($id)
    {
        $notification = $this->getOr404($id);
        if ($notification->getPerson()->getId() !== $this->getUser()->getId()) {
            throw new AccessDeniedHttpException();
        }

        return $this->renderWithContext($notification);
    }

    /**
     * Creates a Notification for the current user
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Creates a new notification from the submitted data.",
     *   input = "PROCERGS\LoginCidadao\CoreBundle\Form\Notification\NotificationType",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     * @REST\View(
     *   template = "PROCERGSLoginCidadaoAPIBundle:Notification:newNotification.html.twig",
     *   statusCode = Codes::HTTP_BAD_REQUEST,
     *   templateVar = "form"
     * )
     *
     * @param Request $request
     * @return FormTypeInterface|View
     *
     * @REST\Post("/person/notification")
     */
    public function postNotificationAction(Request $request)
    {
        try {
            $newNotification = $this->getNotificationHandler()->post(
                    $request->request->all()
            );

            $routeOptions = array(
                'id' => $newNotification->getId(),
                '_format' => $request->get('_format')
            );

            return $this->routeRedirectView('api_1_get_notification',
                            $routeOptions, Codes::HTTP_CREATED);
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }
    }

    /**
     *
     * @return \PROCERGS\LoginCidadao\CoreBundle\Handler\NotificationHandlerInterface
     */
    private function getNotificationHandler()
    {
        return $this->container->get('procergs.notification.handler');
    }

    /**
     * Fetch a Notification or throw an 404 Exception.
     *
     * @param mixed $id
     * @return NotificationInterface
     * @throws NotFoundHttpException
     */
    protected function getOr404($id)
    {
        if (!($notification = $this->getNotificationHandler()->get($id))) {
            throw new NotFoundHttpException(sprintf('The resource \'%s\' was not found.',
                    $id));
        }

        return $notification;
    }

}
