<?php

namespace PROCERGS\LoginCidadao\NotificationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PROCERGS\LoginCidadao\NotificationBundle\Form\BroadcastType;
use PROCERGS\LoginCidadao\NotificationBundle\Form\BroadcastSettingsType;
use PROCERGS\LoginCidadao\NotificationBundle\Form\BroadcastAbout;
use Symfony\Component\HttpFoundation\Request;
use PROCERGS\LoginCidadao\NotificationBundle\Model\BroadcastSettings;
use PROCERGS\LoginCidadao\NotificationBundle\Model\BroadcastPlaceholder;
use PROCERGS\LoginCidadao\NotificationBundle\Entity\Notification;
use PROCERGS\LoginCidadao\CoreBundle\Helper\GridHelper;
use PROCERGS\LoginCidadao\NotificationBundle\Entity\Broadcast;

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
        $sql = $em->getRepository('PROCERGSLoginCidadaoNotificationBundle:Broadcast')->createQueryBuilder('c')
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
        $broadcast = $em->getRepository('PROCERGSLoginCidadaoNotificationBundle:Broadcast')->findOneById($broadcastId);
        
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
        $clients = $em->getRepository('PROCERGSOAuthBundle:Client')->createQueryBuilder('c')
            ->where(':person MEMBER OF c.owners')
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
        $broadcast = $this->getDoctrine()->getRepository('PROCERGSLoginCidadaoNotificationBundle:Broadcast')->find($broadcastId);
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
          $broadcast->setHtmlTemplate($placeholders);                   
          
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
          $notification->setIcon("icon");
          $notification->setCallbackUrl("url");
          $notification->setShortText($shortText);
          $notification->setTitle($title);
          $notification->setHtmlTemplate($html);
          $notification->setPerson($person);
          $notification->setSender($broadcast->getCategory()->getClient());            
          $notification->setCategory($broadcast->getCategory());

          $helper->send($notification);
        }
    }

}
