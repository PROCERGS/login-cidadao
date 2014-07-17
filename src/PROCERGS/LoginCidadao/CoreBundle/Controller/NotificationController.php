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
    /**
     * @Route("/inbox/sidebar", name="lc_not_inbox_sidebar")
     * @Template()
     */
    public function sidebarAction()
    {
        $result = $this->getDoctrine()
            ->getManager ()
            ->getRepository('PROCERGSOAuthBundle:Client')
            ->createQueryBuilder('u')
            ->select('u.id, u.name, count(n.id) total')
            ->join('PROCERGSLoginCidadaoCoreBundle:Notification', 'n', 'WITH', 'n.client = u')
            ->where('u.visible = true')
            ->orWhere('u.id = 1')
            ->orderBy('u.id', 'ASC')
            ->groupBy('u.id, u.name')
            ->getQuery()
            ->getResult();
        return array('clients' => $result);
    }
    
    /**
     * @Route("/inbox/{id}/{title}", name="lc_not_inbox")
     * @Template()
     */
    public function inboxAction($id = null, $title = null)
    {
        if (null === $id) {
            return array();
        }
        $conn = $this->getDoctrine()->getManager()->getConnection();
        $pars = array($id, $this->getUser()->getId());
        if (null !== $title) {
            $sql = 'select u.id, u.title, u.shorttext, u.isread, u.createdat from notification u where u.client_id = ? and u.person_id = ? ';
            $sql .= "and title = ? ";
            $pars[] = $title;
        } else {
            $sql = 'select u.title, count(u.title) as total, count(case when u.isread = true then true else null end) as readed from notification u where u.client_id = ? and u.person_id = ? ';
            $sql .= 'group by u.title order by title';
        }
        $stmt = $conn->prepare($sql);
        $stmt->execute($pars);
        $resultset = $stmt->fetchAll();
        /*
        $result = $this->getDoctrine()
        ->getManager ()
        ->getRepository('PROCERGSLoginCidadaoCoreBundle:Notification')
        ->createQueryBuilder('u')
        ->select('u.title, count(u.title) as total, count(case when u.isread = true then true else null end) as readed')
        ->where('u.id = :id')
        ->setParameter('id' , $id)
        ->groupBy('u.title')
        ->orderBy('u.title', 'ASC')
        ->getQuery()
        ->getResult();
        */
        return compact('resultset', 'id', 'title');
    }
    
    
    /**
     * @Route("/config/{id}", name="lc_not_config")
     * @Template()
     */
    public function configAction($id = null)
    {
        return array();
    }

}
