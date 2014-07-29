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
            ->getRepository('PROCERGSLoginCidadaoCoreBundle:Notification')
            ->createQueryBuilder('n')
            ->select('c.id, c.name, CountIf(n.isRead != true) total')
            ->join('PROCERGSLoginCidadaoCoreBundle:ConfigNotCli', 'cnc', 'WITH', 'n.configNotCli = cnc')
            ->join('PROCERGSOAuthBundle:Client', 'c', 'WITH', 'cnc.client = c')
            ->where('n.person = :person')            
            ->setParameter('person', $this->getUser())
            ->groupBy('c.id', 'c.name')
            ->orderBy('c.id', 'ASC')            
            ->getQuery()
            ->getResult();
        return array('clients' => $result);
    }
    
    /**
     * @Route("/inbox/gridfull", name="lc_not_inbox_gridfull")
     * @Template()
     */    
    public function gridFullAction(Request $request = null) {
        $resultset = $this->getDoctrine()
        ->getManager ()
        ->getRepository('PROCERGSLoginCidadaoCoreBundle:Notification')
        ->createQueryBuilder('n')
        ->join('PROCERGSLoginCidadaoCoreBundle:ConfigNotCli', 'cnc', 'WITH', 'n.configNotCli = cnc')
        ->join('PROCERGSOAuthBundle:Client', 'c', 'WITH', 'cnc.client = c')
        ->where('n.person = :person')
        ->setParameter('person', $this->getUser())
        ->orderBy('n.createdAt', 'DESC')
        ->getQuery()->getResult();
        return array('resultset' => $resultset);
    }
    
    /**
     * @Route("/inbox/gridpri", name="lc_not_inbox_gridpri")
     * @Template()
     */
    public function gridPriAction(Request $request = null) {
        $id = $request->get('id');
        if (!$id) {
            return $this->gridFullAction();
        }
        $resultset = $this->getDoctrine()
        ->getManager()
        ->getRepository('PROCERGSLoginCidadaoCoreBundle:ConfigNotCli')
        ->createQueryBuilder('cnc')        
        ->join('PROCERGSOAuthBundle:Client', 'c', 'WITH', 'cnc.client = c')
        ->where('c.id = :client')
        ->setParameter('client', $id)
        ->getQuery()->getResult();
        return array('resultset' => $resultset);
    }
    
    /**
     * @Route("/inbox/gridsimple", name="lc_not_inbox_gridfull")
     * @Template()
     */
    public function gridSimpleAction(Request $request = null) {
        $id = $request->get('id');
        if (!$id) {
            return $this->gridFullAction();
        }
        $resultset = $this->getDoctrine()
        ->getManager ()
        ->getRepository('PROCERGSLoginCidadaoCoreBundle:Notification')
        ->createQueryBuilder('n')
        ->join('PROCERGSLoginCidadaoCoreBundle:ConfigNotCli', 'cnc', 'WITH', 'n.configNotCli = cnc')
        ->join('PROCERGSOAuthBundle:Client', 'c', 'WITH', 'cnc.client = c')
        ->where('n.person = :person and cnc.id = :configNotCli')
        ->setParameter('person', $this->getUser())
        ->setParameter('configNotCli', $id)
        ->orderBy('n.createdAt', 'DESC')
        ->getQuery()->getResult();
        return array('resultset' => $resultset);
    }
    
    /**
     * @Route("/inbox", name="lc_not_inbox")
     * @Template()
     */
    public function inboxAction(Request $request)
    {
        if ($request->get('id')) {
            return $this->render('PROCERGSLoginCidadaoCoreBundle:Notification:inbox2.html.twig',
                array('id' => $request->get('id'))
            );
        } else if ($request->get('notification')) {
            $resultset = $this->getDoctrine()
            ->getManager ()
            ->getRepository('PROCERGSLoginCidadaoCoreBundle:Notification')
            ->createQueryBuilder('n')
            ->join('PROCERGSLoginCidadaoCoreBundle:ConfigNotCli', 'cnc', 'WITH', 'n.configNotCli = cnc')
            ->join('PROCERGSOAuthBundle:Client', 'c', 'WITH', 'cnc.client = c')
            ->where('n.person = :person and n.id = :id')
            ->setParameter('person', $this->getUser())
            ->setParameter('id', $request->get('notification'))
            ->getQuery()->getOneOrNullResult();
            if ($resultset) {
                $resultset->setIsRead(true);
                $this->getDoctrine()->getManager()->persist($resultset);
                $this->getDoctrine()->getManager()->flush();
            }
            return $this->render('PROCERGSLoginCidadaoCoreBundle:Notification:inbox3.html.twig',
                array('resultset' => $resultset)
            );
        } else {
            return array();
        }
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
