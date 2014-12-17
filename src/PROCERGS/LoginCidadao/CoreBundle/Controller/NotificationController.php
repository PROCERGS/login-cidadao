<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PROCERGS\LoginCidadao\NotificationBundle\Entity\Notification;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use PROCERGS\LoginCidadao\CoreBundle\Helper\GridHelper;
use PROCERGS\LoginCidadao\CoreBundle\Entity\ConfigNotPer;
use PROCERGS\LoginCidadao\CoreBundle\Form\Type\ClientNotPerFormType;
use Symfony\Component\HttpFoundation\Response;
use PROCERGS\LoginCidadao\NotificationBundle\Entity\PersonNotificationOption;
use PROCERGS\LoginCidadao\CoreBundle\Form\Type\PersonNotificationOptionFormType;
use Symfony\Component\Form\FormError;
use Doctrine\ORM\Mapping\ClassMetadata;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Notification\Category;
use PROCERGS\LoginCidadao\NotificationBundle\Model\NotificationIterable;

class NotificationController extends Controller
{

    /**
     * @Route("/inbox/sidebar", name="lc_not_inbox_sidebar")
     * @Template()
     */
    public function sidebarAction()
    {
        $result = $this->getDoctrine()
            ->getManager()
            ->getRepository('PROCERGSLoginCidadaoNotificationBundle:Notification')
            ->getTotalUnreadGroupByClient($this->getUser());
        return array('clients' => $result);
    }

    /**
     * @Route("/inbox/gridnavbarunread", name="lc_not_inbox_gridnavbarunread")
     * @Template()
     */
    public function gridNavbarUnreadAction(Request $request)
    {
        $handler = $this->getNotificationHandler()
            ->getAuthenticatedHandler($this->getUser());
        $iterator = new NotificationIterable($handler, 8);

        $grid = new GridHelper($iterator);
        $grid->setId('navbarUnread');
        $grid->setPerPage(8);
        $grid->setMaxResult(8);
        $grid->setInfiniteGrid(true);
        $grid->setRoute('lc_not_inbox_gridnavbarunread');
        $grid->setRouteParams(array('client', 'mode', 'notification', 'confignotcli'));
        $grid->setExtraOpts(array('behavior' => 'local', 'binder' => 'div:has(#navbarUnread):last'));
        return array('grid' => $grid->createView($request));
    }

    /**
     * @Route("/inbox/gridfull", name="lc_not_inbox_gridfull")
     * @Template()
     */
    public function gridFullAction(Request $request = null) {
        $sql = $this->getDoctrine()
        ->getManager ()
        ->getRepository('PROCERGSLoginCidadaoCoreBundle:Notification\Notification')
        ->createQueryBuilder('n')
        ->select('n.id, case when n.readDate is null then false else true end isread, n.title, n.shortText shorttext, n.createdAt createdat, c.id client_id, c.name client_name, c.picturePath client_picture, cnc.defaultIcon category_default_icon')
        ->join('PROCERGSLoginCidadaoCoreBundle:Notification\Category', 'cnc', 'WITH', 'n.category = cnc')
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
        ->getRepository('PROCERGSLoginCidadaoCoreBundle:Notification\Category')
        ->createQueryBuilder('cnc')
        ->select('cnc')
        ->join('PROCERGSOAuthBundle:Client', 'c', 'WITH', 'cnc.client = c')
        ->where('c.id = :client')
        ->setParameter('client', $id)
        ->getQuery()->setFetchMode('PROCERGS\LoginCidadao\CoreBundle\Entity\Notification\Category', 'client', ClassMetadata::FETCH_EAGER)->getResult();
        $grids = array();
        foreach ($resultset as $rows) {
            $request->query->set('confignotcli', $rows->getId());
            $temp = $this->gridSimpleAction();
            $grids[$rows->getId()] = $temp['grid'];
        }
        return array('resultset' => $resultset, 'grids' => $grids);
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
        ->getRepository('PROCERGSLoginCidadaoCoreBundle:Notification\Notification')
        ->createQueryBuilder('n')
        ->select('n.id, case when n.readDate is null then false else true end isread, n.title, n.shortText shorttext, n.createdAt createdat, c.id client_id, c.name client_name')
        ->join('PROCERGSLoginCidadaoCoreBundle:Notification\Category', 'cnc', 'WITH', 'n.category = cnc')
        ->join('PROCERGSOAuthBundle:Client', 'c', 'WITH', 'cnc.client = c')
        ->where('n.person = :person and cnc.id = :configNotCli')
        ->setParameter('person', $this->getUser())
        ->setParameter('configNotCli', $id)
        ->orderBy('n.id', 'DESC');

        if ($request->get('client')) {
            $sql->andWhere('c.id = :client')->setParameter('client', $request->get('client'));
        }

        $grid = new GridHelper();
        $grid->setId('simpleOne'.$id);
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
        $mode = $request->get('mode', 0);
        if ($mode === 0) {
            $return = $this->gridFullAction($request);
            if ($request->get('client')) {
                $client = $this->getDoctrine()->getManager()->getRepository('PROCERGSOAuthBundle:Client')->find($request->get('client'));
                $return['extra_title'] = $client->getName();
            } else {
                $return['extra_title'] = $this->get('translator')->trans('notification.menu.inbox.all');
            }
            return $return;
        } else {
            return $this->gridPriAction($request);
        }
    }

    /**
     * @Route("/inbox/show2", name="lc_not_inbox_show2")
     */
    public function show2Action(Request $request)
    {
        $em = $this->getDoctrine()->getManager ();
        $resultset = $em
        ->getRepository('PROCERGSLoginCidadaoCoreBundle:Notification\Notification')
        ->createQueryBuilder('n')
        ->join('PROCERGSLoginCidadaoCoreBundle:Notification\Category', 'cnc', 'WITH', 'n.category = cnc')
        ->join('PROCERGSOAuthBundle:Client', 'c', 'WITH', 'cnc.client = c')
        ->where('n.person = :person and n.id = :id')
        ->setParameter('person', $this->getUser())
        ->setParameter('id', $request->get('notification'))
        ->getQuery()->getOneOrNullResult();
        $a = array('wasread' => false, 'htmltpl' => null);
        if ($resultset) {
            if (!$resultset->isRead()) {
                $resultset->setReadDate(new \DateTime());
                $em->persist($resultset);
                $em->flush();
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
        ->getRepository('PROCERGSLoginCidadaoCoreBundle:Notification\Category')
        ->createQueryBuilder('cnc')
        ->select('cnc, cnp')
        ->join('PROCERGSOAuthBundle:Client', 'c', 'WITH', 'cnc.client = c')
        ->leftJoin('PROCERGSLoginCidadaoCoreBundle:Notification\PersonNotificationOption', 'cnp', 'with', 'cnp.category = cnc and cnp.person = :person')
        ->where('c.id = :client ')
        ->setParameter('client', $id)
        ->setParameter('person', $this->getUser())
        ->getQuery()->setFetchMode('PROCERGSLoginCidadaoCoreBundle:Notification\Category', 'client', ClassMetadata::FETCH_EAGER) ->getResult();
        for ($i = 0, $tot = count($resultset); $i < $tot; $i++) {
            if ($i%2) {
                $c =& $resultset[$i];
                if (!$c) {
                    $res =& $resultset[$i-1];
                    $a = new PersonNotificationOption();
                    $a->setPerson($this->getUser());
                    $a->setCategory($res);
                    $a->setSendEmail($res->getEmailable());
                    $a->setSendPush(false);
                    $a->setCreatedAt(new \DateTime());
                    $em->persist($a);
                    $c =& $a;
                }
                $formId = 'person-notifcation-category-'.$resultset[$i-1]->getId();
                $form = $this->createForm(new PersonNotificationOptionFormType(), $c, array(
                    'action'=> $this->generateUrl('lc_not_config_change'),
                    'attr' => array(
                        'class' => 'form-ajax',
                        'id' => $formId,
                        'role' => 'form',
                        'ajax-target' => 'div:has(#'. $formId .'):last'
                    )
                ));
                $forms[$i-1] = $form->createView();
                unset($resultset[$i]);
            }
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
        $form = $this->createForm(new PersonNotificationOptionFormType());
        $config = null;
        $manager = $this->getDoctrine()->getManager();
        if ((($data = $request->get($form->getName())) && ($id = $data['id']))) {
            $config = $manager->getRepository('PROCERGSLoginCidadaoNotificationBundle:Category')
            ->createQueryBuilder('cnc')
            ->select('cnp')
                    ->join('PROCERGSOAuthBundle:Client', 'c', 'WITH',
                           'cnc.client = c')
                    ->join('PROCERGSLoginCidadaoNotificationBundle:PersonNotificationOption',
                           'cnp', 'with',
                           'cnp.category = cnc and cnp.person = :person')
            ->where('cnp.id = :client ')
            ->setParameter('client', $id)
            ->setParameter('person', $this->getUser())
            ->getQuery()->setFetchMode('PROCERGSLoginCidadaoCoreBundle:Notification\PersonNotificationOption', 'category', ClassMetadata::FETCH_EAGER)->getOneOrNullResult();
        }
        if (!$config) {
            die('dunno');
        }
        $formId = 'person-notifcation-category-'.$config->getCategory()->getId();
        $form = $this->createForm(new PersonNotificationOptionFormType(), $config, array(
            'action'=> $this->generateUrl('lc_not_config_change'),
            'attr' => array(
                'class' => 'form-ajax',
                'id' => $formId,
                'role' => 'form',
                'ajax-target' => 'div:has(#'. $formId .'):last'
            )
        ));
        $form->handleRequest($this->getRequest());
        if ($form->isValid()) {
            $manager->persist($config);
            $manager->flush();
        }
        $message = "notification.config.category.change.success";
        //$translator = $this->get('translator');
        //$form->addError(new FormError($translator->trans("notification.missing.personnotificationoption")));
        return array('form' => $form->createView(), 'form_message' => $message);
    }

    /**
     * @return NotificationHandlerInterface
     */
    private function getNotificationHandler()
    {
        return $this->get('procergs.notification.handler');
    }

}
