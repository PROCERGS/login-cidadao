<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Notification;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class NotificationController extends Controller
{

    /**
     * @Route("/list", name="lc_notifications_list")
     * @Template()
     */
    public function listAction()
    {
        $em = $this->getDoctrine()->getManager();

        $notifications = $em->getRepository("PROCERGSLoginCidadaoCoreBundle:Notification")
                ->findBy(array('person' => $this->getUser()));

        return compact('notifications');
    }

    /**
     * @Route("/read", name="lc_notifications_read")
     * @Template()
     */
    public function readAction(Request $request)
    {
        $notificationId = $request->get('notification');

        $em = $this->getDoctrine()->getManager();
        $notifications = $em->getRepository('PROCERGSLoginCidadaoCoreBundle:Notification');

        $data = array('status' => 0, 'message' => 'Error');

        try {
            $notification = $notifications->find($notificationId);
            if ($notification && !$notification->isExtreme()) {
                $notification->setIsRead(true);
                $em->flush();
                $data = array('status' => 1, 'message' => 'Done');
            }

        } catch (\Exception $e) {
            $data = array('status' => 0, 'message' => 'Error');
        }

        $response = new JsonResponse();
        $response->setData($data);

        return $response;
    }

}
