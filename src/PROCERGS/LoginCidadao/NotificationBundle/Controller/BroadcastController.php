<?php

namespace PROCERGS\LoginCidadao\NotificationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PROCERGS\LoginCidadao\NotificationBundle\Form\BroadcastType;
use PROCERGS\LoginCidadao\NotificationBundle\Form\BroadcastSettingsType;
use Symfony\Component\HttpFoundation\Request;
use PROCERGS\LoginCidadao\NotificationBundle\Model\BroadcastSettings;
use PROCERGS\LoginCidadao\NotificationBundle\Model\BroadcastPlaceholder;
use PROCERGS\LoginCidadao\NotificationBundle\Entity\Broadcast;
use PROCERGS\LoginCidadao\NotificationBundle\Entity\Notification;
use PROCERGS\LoginCidadao\NotificationBundle\Helper\NotificationsHelper;

class BroadcastController extends Controller
{

    /**
     * @Route("/notifications/broadcasts", name="lc_notification_broadcast_list")
     * @Template()
     */
    public function listAction()
    {
        $em = $this->getDoctrine()->getManager();
        $apps = $em->getRepository('PROCERGSOAuthBundle:Client')->createQueryBuilder('c')
            ->where(':person MEMBER OF c.owners')
            ->setParameter('person', $this->getUser())
            ->addOrderBy('c.id', 'desc')
            ->getQuery()
            ->getResult();

        return array('apps' => $apps);
    }


    /**
     * @Route("/notifications/broadcasts/new/{clientId}", name="lc_notification_broadcast_new")
     * @Template()
     */
    public function newAction(Request $request, $clientId)
    {
        $form = $this->createForm(new BroadcastType($this->getUser(), $clientId));

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $broadcast = $form->getData();
            $em->persist($broadcast);
            $em->flush();
            $url = $this->generateUrl('lc_notification_broadcast_settings',
              array('broadcastId' => $broadcast->getId()));
            return $this->redirect($url);
        }

        return array('form' => $form->createView(), 'clientId' => $clientId);
    }

    /**
     * @Route("/notifications/broadcasts/{broadcastId}/settings", name="lc_notification_broadcast_settings")
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
          $em = $this->getDoctrine()->getManager();
          $placeholders = $form->get('placeholders')->getData();
          $broadcast->setHtmlTemplate($placeholders);
          $em->persist($broadcast);
          $em->flush();

          
          $helper = $this->get('notifications.helper');          
          $shortText = $form->get('shortText')->getData();
          $title = $form->get('title')->getData();
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
          die('feito');
        }

        return array('form' => $form->createView());
    }

}
