<?php

namespace LoginCidadao\NotificationBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use LoginCidadao\NotificationBundle\Handler\AuthenticatedNotificationHandlerInterface;
use LoginCidadao\NotificationBundle\Handler\NotificationHandlerInterface;
use LoginCidadao\NotificationBundle\Model\NotificationIterable;
use LoginCidadao\NotificationBundle\Model\ClientNotificationIterable;
use LoginCidadao\CoreBundle\Helper\GridHelper;
use LoginCidadao\OAuthBundle\Model\ClientInterface;

class NotificationController extends Controller
{

    /**
     * @Route("/notifications/{id}", requirements={"id" = "\d+"}, defaults={"id" = null}, name="lc_notifications")
     * @Template()
     */
    public function indexAction(Request $request, $id = null)
    {
        if (null !== $id) {
            $openId = $id;
            $offset = $id + 1;
            $this->getAuthenticatedNotificationHandler()->markRangeAsRead($openId,
                $openId);
        } else {
            $offset = null;
        }

        $grid = $this->getNotificationGrid($offset, 10)->createView($request);

        return compact('grid', 'openId');
    }

    /**
     * @Route("/client/{clientId}/notifications/{id}", requirements={"id" = "\d+", "clientId" = "\d+"}, defaults={"id" = null}, name="lc_notifications_from_client")
     * @Template("LoginCidadaoNotificationBundle:Notification:index.html.twig")
     */
    public function getFromClientAction(Request $request, $clientId, $id = null)
    {
        if (null !== $id) {
            $openId = $id;
            $offset = $id + 1;
            $this->getAuthenticatedNotificationHandler()->markRangeAsRead($openId,
                $openId);
        } else {
            $offset = null;
        }

        $client = $this->getDoctrine()->getRepository('LoginCidadaoOAuthBundle:Client')
            ->find($clientId);

        $grid = $this->getNotificationGrid($offset, 10, $client)->createView($request);

        return compact('grid', 'openId');
    }

    /**
     * @Route("/notifications/clients/settings/{clientId}", requirements={"clientId" = "\d+"}, defaults={"clientId" = null}, name="lc_notifications_settings")
     * @Template()
     */
    public function editSettingsAction(Request $request, $clientId = null)
    {
        $person = $this->getUser();
        if (null !== $clientId) {
            $client = $this->getDoctrine()
                ->getRepository('LoginCidadaoOAuthBundle:Client')
                ->find($clientId);
            if (!$client instanceof ClientInterface || !$person->hasAuthorization($client)) {
                throw new AccessDeniedHttpException();
            }
        } else {
            $client = null;
        }

        $handler = $this->getNotificationHandler()
            ->getAuthenticatedHandler($person);
        $handler->initializeSettings();

        $form = $this->createForm('LoginCidadao\NotificationBundle\Form\SettingsType',
            $handler->getGroupedSettings($client));

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $form->getData()->persist($em);
            $em->flush();
            return $this->redirect($this->generateUrl('lc_notifications_settings'));
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/notifications/grid/fragment/{offset}", requirements={"offset" = "\d+"}, defaults={"offset" = 0}, name="lc_notifications_grid_fragment")
     * @Template("LoginCidadaoNotificationBundle:Notification:grid.html.twig")
     */
    public function getGridFragmentAction(Request $request, $offset = 0)
    {
        $grid = $this->getNotificationGrid($offset, 10)->createView($request);

        return compact('grid');
    }

    /**
     * @Route("/client/{clientId}/notifications/grid/fragment/{offset}", requirements={"offset" = "\d+", "clientId" = "\d+"}, defaults={"offset" = 0}, name="lc_client_notifications_grid_fragment")
     * @Template("LoginCidadaoNotificationBundle:Notification:grid.html.twig")
     */
    public function getGridClientFragmentAction(Request $request, $clientId,
                                                $offset = 0)
    {
        $client = $this->getDoctrine()->getRepository('LoginCidadaoOAuthBundle:Client')
            ->find($clientId);
        $grid   = $this->getNotificationGrid($offset, 10, $client)->createView($request);

        return compact('grid');
    }

    /**
     * @return AuthenticatedNotificationHandlerInterface
     */
    private function getAuthenticatedNotificationHandler()
    {
        return $this->getNotificationHandler()
                ->getAuthenticatedHandler($this->getUser());
    }

    /**
     * @return NotificationHandlerInterface
     */
    private function getNotificationHandler()
    {
        return $this->get('lc.notification.handler');
    }

    private function getNotificationGrid($offset = 0, $perIteration = 10,
                                            ClientInterface $client = null)
    {
        $handler = $this->getAuthenticatedNotificationHandler();
        if ($client instanceof ClientInterface) {
            $iterator = new ClientNotificationIterable($handler, $client,
                $perIteration, $offset);
        } else {
            $iterator = new NotificationIterable($handler, $perIteration,
                $offset);
        }

        $grid = new GridHelper($iterator);
        $grid->setId('notificationInfiniteGrid');
        $grid->setPerPage(10);
        $grid->setMaxResult(10);
        $grid->setInfiniteGrid(true);

        $routeParams = array('offset');
        if ($client instanceof ClientInterface) {
            $grid->setRoute('lc_client_notifications_grid_fragment');
            $routeParams[] = 'clientId';
        } else {
            $grid->setRoute('lc_notifications_grid_fragment');
        }
        $grid->setRouteParams($routeParams);

        return $grid;
    }

    /**
     * @Route("/notifications/sidebar", name="lc_notifications_sidebar")
     * @Template()
     */
    public function sidebarAction($route, $clientId)
    {
        $handler = $this->getNotificationHandler()
            ->getAuthenticatedHandler($this->getUser());

        $clients = $handler->countUnreadByClient();
        return compact('clients', 'route', 'clientId');
    }

    /**
     * @Route("/notifications/navbar/{offset}", requirements={"offset" = "\d+"}, defaults={"offset" = 0}, name="lc_notifications_navbar")
     * @Template()
     */
    public function navbarAction(Request $request, $offset = 0)
    {
        $grid = $this->getNotificationGrid($offset, 5)
            ->setPerPage(5)
            ->setMaxResult(5)
            ->setRoute('lc_notifications_navbar')
            ->createView($request);

        return compact('grid');
    }
}
