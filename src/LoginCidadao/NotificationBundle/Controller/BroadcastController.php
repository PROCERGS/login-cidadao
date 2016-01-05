<?php

namespace LoginCidadao\NotificationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use LoginCidadao\NotificationBundle\Form\BroadcastType;
use LoginCidadao\NotificationBundle\Form\BroadcastSettingsType;
use LoginCidadao\NotificationBundle\Form\BroadcastAbout;
use Symfony\Component\HttpFoundation\Request;
use LoginCidadao\NotificationBundle\Model\BroadcastSettings;
use LoginCidadao\NotificationBundle\Model\BroadcastPlaceholder;
use LoginCidadao\NotificationBundle\Entity\Notification;
use LoginCidadao\CoreBundle\Helper\GridHelper;
use LoginCidadao\NotificationBundle\Entity\Broadcast;
use LoginCidadao\NotificationBundle\Handler\NotificationHandler;

/**
 * @Route("/dev/broadcasts")
 */
class BroadcastController extends Controller
{

    /**
     * @Route("/list/{id}", requirements={"id" = "\d+"}, defaults={"id" = null}, name="lc_dev_broadcasts")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $grid = $this->getBroadcastGrid();
        return array('grid' => $grid->createView($request));
    }

    private function getBroadcastGrid(){
        $em = $this->getDoctrine()->getManager();
        $sql = $em->getRepository('LoginCidadaoNotificationBundle:Broadcast')->createQueryBuilder('c')
            ->where('c.person = :person')
            ->setParameter('person', $this->getUser())
            ->addOrderBy('c.id', 'desc');

        $grid = new GridHelper();
        $grid->setId('broadcasts');
        $grid->setPerPage(10);
        $grid->setMaxResult(10);
        $grid->setQueryBuilder($sql);
        $grid->setInfiniteGrid(true);
        $grid->setRoute('lc_dev_broadcasts');

        return $grid;
    }

    /**
     * @Route("/about/{broadcastId}/", name="lc_dev_broadcast_about")
     * @Template()
     */
    public function aboutAction(Request $request, $broadcastId)
    {
        $em = $this->getDoctrine()->getManager();
        $broadcast = $em->getRepository('LoginCidadaoNotificationBundle:Broadcast')->findOneById($broadcastId);

        $form = $this->createForm(new BroadcastAbout($broadcast->getId()));
        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->sendBroadcast($broadcast, $broadcast->getShortText(), $broadcast->getTitle());
            $em = $this->getDoctrine()->getManager();
            $broadcast->setSent(true);
            $em->persist($broadcast);
            $em->flush();

            $translator = $this->get('translator');
            $this->get('session')->getFlashBag()->add('success', $translator->trans("Broadcast sent"));
            return $this->redirect($this->generateUrl('lc_dev_broadcasts'));
        }

        return array('broadcast' => $broadcast, 'form' => $form->createView());
    }

    /**
     * @Route("/clients", name="lc_dev_broadcasts_clients")
     * @Template()
     */
    public function clientsAction()
    {
        $em = $this->getDoctrine()->getManager();
        $clients = $em->getRepository('LoginCidadaoOAuthBundle:Client')->createQueryBuilder('c')
            ->where(':person MEMBER OF c.owners')
            ->innerJoin('LoginCidadaoNotificationBundle:Category', 'cat', 'WITH',
                        'c.id = cat.client')
            ->setParameter('person', $this->getUser())
            ->addOrderBy('c.id', 'desc')
            ->getQuery()
            ->getResult();

        return array('clients' => $clients);
    }


    /**
     * @Route("/new/{clientId}", name="lc_dev_broadcast_new")
     * @Template()
     */
    public function newAction(Request $request, $clientId)
    {
        $form = $this->createForm(new BroadcastType($this->getUser(), $clientId));

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $broadcast = $form->getData();
            $broadcast->setPerson($this->getUser());
            $em->persist($broadcast);
            $em->flush();
            $url = $this->generateUrl('lc_dev_broadcast_settings',
              array('broadcastId' => $broadcast->getId()));
            return $this->redirect($url);
        }

        return array('form' => $form->createView(), 'clientId' => $clientId);
    }

    /**
     * @Route("/settings/{broadcastId}", name="lc_dev_broadcast_settings")
     * @Template()
     */
    public function settingsAction(Request $request, $broadcastId)
    {
        $broadcast = $this->getDoctrine()->getRepository('LoginCidadaoNotificationBundle:Broadcast')->find($broadcastId);
        $category = $broadcast->getCategory();
        $placeholders = $category->getPlaceholders();

        $broadcastSettings = new BroadcastSettings($broadcast);
        foreach ($placeholders as $placeholder) {
            $broadcastSettings->getPlaceholders()->add(new BroadcastPlaceholder($placeholder));
        }

        $form = $this->createForm(new BroadcastSettingsType($broadcastId, $category->getId()), $broadcastSettings);

        $form->handleRequest($request);
        if ($form->isValid()) {
          $placeholders = $form->get('placeholders')->getData();
          $broadcast->setHtmlTemplate($placeholders, $form->get('title')->getData(), $form->get('shortText')->getData());

          $translator = $this->get('translator');
          if ($form->get('saveAndAdd')->isClicked()) {
            $this->sendBroadcast($broadcast, $form->get('shortText')->getData(), $form->get('title')->getData());
            $broadcast->setSent(true);
            $this->get('session')->getFlashBag()->add('success', $translator->trans("Broadcast sent"));
          } else {
            $this->get('session')->getFlashBag()->add('success', $translator->trans("Broadcast saved"));
          }

          $em = $this->getDoctrine()->getManager();
          $em->persist($broadcast);
          $em->flush();

          return $this->redirect($this->generateUrl('lc_dev_broadcasts'));
        }

        return array('form' => $form->createView());
    }

    private function sendBroadcast(Broadcast $broadcast, $shortText, $title) {
        $helper = $this->get('notifications.helper');
        $html = $broadcast->getHtmlTemplate();

        foreach ($broadcast->getReceivers() as $person) {
          $notification = new Notification();
          $notification->setIcon($broadcast->getCategory()->getDefaultIcon());
          //$notification->setCallbackUrl("url");
          $notification->setShortText($shortText);
          $notification->setTitle($title);
          $notification->setHtmlTemplate($html);
          $notification->setPerson($person);
          $notification->setSender($broadcast->getCategory()->getClient());
          $notification->setCategory($broadcast->getCategory());
          $notification->setMailTemplate($broadcast->getMailTemplate());

          $helper->send($notification);
        }
    }

    /**
     * @Route("/grid/receivers/filter", name="lc_dev_broadcasts_grid_receivers_filter")
     * @Template()
     */
    public function gridReceiversFilterAction(Request $request)
    {
        $grid = new GridHelper();
        $grid->setId('receivers-filter-grid');
        $grid->setPerPage(5);
        $grid->setMaxResult(5);
        $parms = $request->get('ac_data');
        if (!isset($parms['client_id']) || !is_numeric($parms['client_id'])) {
            die('dunno');
        }
        if (isset($parms['username'])) {
            $em = $this->getDoctrine()->getManager();
            $sql = $em->getRepository('LoginCidadaoCoreBundle:Person')->getFindAuthorizedByClientIdQuery($parms['client_id']);
            $sql->andWhere('p.cpf like ?1 or p.username like ?1 or p.email like ?1 or p.firstName like ?1 or p.surname like ?1');
            $sql->setParameter('1',
                '%' . addcslashes($parms['username'], '\\%_') . '%');
            $sql->addOrderBy('p.id', 'desc');
            $grid->setQueryBuilder($sql);
        }
        $grid->setInfiniteGrid(true);
        $grid->setRouteParams(array('ac_data'));
        $grid->setRoute('lc_dev_broadcasts_grid_receivers_filter');
        return array('grid' => $grid->createView($request));
    }

    /**
     * @Route("/grid/receivers", name="lc_dev_broadcasts_grid_receivers")
     * @Template()
     */
    public function gridReceiversAction(Request $request)
    {
        $grid = new GridHelper();
        $grid->setId('receivers-grid');
        $grid->setPerPage(5);
        $grid->setMaxResult(5);
        $parms = $request->get('ac_data');
        if (isset($parms['person_id']) && !empty($parms['person_id'])) {
            $em = $this->getDoctrine()->getManager();
            $sql = $em->getRepository('LoginCidadaoCoreBundle:Person')->getFindAuthorizedByClientIdQuery($parms['client_id']);
            $sql->where('p.id in(:id)')->setParameter('id', $parms['person_id']);
            $sql->addOrderBy('p.id', 'desc');
            $grid->setQueryBuilder($sql);
        }
        $grid->setInfiniteGrid(true);
        $grid->setRouteParams(array('ac_data'));
        $grid->setRoute('lc_dev_broadcasts_grid_receivers');
        return array('grid' => $grid->createView($request));
    }

}
