<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Notification;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use PROCERGS\LoginCidadao\CoreBundle\Helper\GridHelper;
use PROCERGS\LoginCidadao\CoreBundle\Entity\ConfigNotPer;
use PROCERGS\LoginCidadao\CoreBundle\Form\Type\ClientNotPerFormType;
use Symfony\Component\HttpFoundation\Response;

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
        $sql = $this->getDoctrine()
        ->getManager ()
        ->getRepository('PROCERGSLoginCidadaoCoreBundle:Notification')
        ->createQueryBuilder('n')
        ->select('n.id, n.isRead isread, n.title, n.shortText shorttext, n.createdAt createdat, c.name client_name')
        ->join('PROCERGSLoginCidadaoCoreBundle:ConfigNotCli', 'cnc', 'WITH', 'n.configNotCli = cnc')
        ->join('PROCERGSOAuthBundle:Client', 'c', 'WITH', 'cnc.client = c')
        ->where('n.person = :person')
        ->setParameter('person', $this->getUser())
        ->orderBy('n.id', 'DESC');
        
        if ($request->get('client')) {
            $sql->andWhere('c.id = :client')->setParameter('client', $request->get('client'));
        }
        if ($request->get('confignotcli')) {
            $sql->andWhere('cnc.id = :confignotcli')->setParameter('confignotcli', $request->get('confignotcli'));
        }        
        $grid = new GridHelper();
        $grid->setId('fullOne');
        $grid->setPerPage(10);
        $grid->setMaxResult(10);
        $grid->setQueryBuilder($sql);
        $grid->setInfinityGrid(true);
        $grid->setRoute('lc_not_inbox');
        $grid->setRouteParams(array('client', 'mode', 'notification', 'confignotcli'));
        return array('grid' => $grid->createView($request));
    }
    
    /**
     * @Route("/inbox/gridpri", name="lc_not_inbox_gridpri")
     * @Template()
     */
    public function gridPriAction(Request $request = null) {
        $id = $request->get('client');
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
     * @Route("/inbox/gridsimple", name="lc_not_inbox_gridsimple")
     * @Template()
     */
    public function gridSimpleAction() {
        $request = $this->getRequest();
        $id = $request->get('confignotcli');
        if (!$id) {
            return $this->gridFullAction();
        }
        $sql = $this->getDoctrine()
        ->getManager ()
        ->getRepository('PROCERGSLoginCidadaoCoreBundle:Notification')
        ->createQueryBuilder('n')
        ->select('n.id, n.isRead isread, n.title, n.shortText shorttext, n.createdAt createdat, c.name client_name')
        ->join('PROCERGSLoginCidadaoCoreBundle:ConfigNotCli', 'cnc', 'WITH', 'n.configNotCli = cnc')
        ->join('PROCERGSOAuthBundle:Client', 'c', 'WITH', 'cnc.client = c')
        ->where('n.person = :person and cnc.id = :configNotCli')
        ->setParameter('person', $this->getUser())
        ->setParameter('configNotCli', $id)
        ->orderBy('n.id', 'DESC');

        if ($request->get('client')) {
            $sql->andWhere('c.id = :client')->setParameter('client', $request->get('client'));
        }
        
        $grid = new GridHelper();
        $grid->setId('simpleOne');
        $grid->setPerPage(10);
        $grid->setMaxResult(10);
        $grid->setQueryBuilder($sql);
        $grid->setInfinityGrid(true);
        $grid->setRoute('lc_not_inbox_gridsimple');
        $grid->setRouteParams(array('client', 'mode', 'notification', 'confignotcli'));
        return array('grid' => $grid->createView($request));
    }
    
    /**
     * @Route("/inbox", name="lc_not_inbox")
     * @Template()
     */
    public function inboxAction(Request $request)
    {
        return array();
    }

    /**
     * @Route("/inbox/show2", name="lc_not_inbox_show2")
     */
    public function show2Action(Request $request)
    {
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
        $a = array('wasread' => false, 'htmltpl' => null);
        if ($resultset) {
            if (!$resultset->getIsRead()) {
                $resultset->setIsRead(true);
                $this->getDoctrine()->getManager()->persist($resultset);
                $this->getDoctrine()->getManager()->flush();
                $a['wasread'] = true;
            }            
            $a['htmltpl'] = $resultset->getHtmlTpl();
        }
        return new JsonResponse($a);
    }
    
    /**
     * @Route("/config", name="lc_not_config")
     * @Template()
     */
    public function configAction(Request $request)
    {
        $id = $request->get('client');
        if (!$id) {
            return $this->gridFullAction();
        }
        $em = $this->getDoctrine()->getManager();
        $resultset = $em
        ->getRepository('PROCERGSLoginCidadaoCoreBundle:ConfigNotCli')
        ->createQueryBuilder('cnc')
        ->join('PROCERGSOAuthBundle:Client', 'c', 'WITH', 'cnc.client = c')
        ->leftJoin('PROCERGSLoginCidadaoCoreBundle:ConfigNotPer', 'cnp', 'with', 'cnp.configNotCli = cnc and cnp.person = :person')
        ->where('c.id = :client ')
        ->setParameter('client', $id)
        ->setParameter('person', $this->getUser())
        ->getQuery()->getResult();
        foreach ($resultset as &$res) {
            $c = $res->getConfigNotPer();
            if (!$c) {
                $a = new ConfigNotPer();
                $a->setPerson($this->getUser());
                $a->setConfigNotCli($res);
                $a->setMailSend($res->getMailSend());
                $em->persist($a);
                $res->setConfigNotPer($a);
            }
            $forms[] = $this->createForm(new ClientNotPerFormType(), $c)->createView();
        }
        if (isset($a)) {
            $em->flush();
        }
        return array('resultset' => $resultset, 'forms' => $forms);
    }
    
    /**
     * @Route("/config/change", name="lc_not_config_change")
     * @Template()
     */
    public function configChangeAction(Request $request)
    {
        $form = $this->createForm(new ClientNotPerFormType());
        $config = null;
        $manager = $this->getDoctrine()->getManager();
        if ((($data = $request->get($form->getName())) && ($id = $data['id']))) {
            $config = $manager->getRepository('PROCERGSLoginCidadaoCoreBundle:ConfigNotCli')
            ->createQueryBuilder('cnc')
            ->select('cnp')
            ->join('PROCERGSOAuthBundle:Client', 'c', 'WITH', 'cnc.client = c')
            ->join('PROCERGSLoginCidadaoCoreBundle:ConfigNotPer', 'cnp', 'with', 'cnp.configNotCli = cnc and cnp.person = :person')
            ->where('cnp.id = :client ')
            ->setParameter('client', $id)
            ->setParameter('person', $this->getUser())
            ->getQuery()->getOneOrNullResult();
        }
        if (!$config) {
            return $this->gridFullAction();
        }
        $form = $this->createForm(new ClientNotPerFormType(), $config);
        $form->handleRequest($this->getRequest());
        if ($form->isValid()) {
            $manager->persist($config);
            $manager->flush();
        }
        return array('form' => $form->createView(), 'cnc_id' => $config->getConfigNotCli()->getId());
    }    
    
    
}
