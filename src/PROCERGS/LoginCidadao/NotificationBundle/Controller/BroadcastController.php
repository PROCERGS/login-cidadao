<?php

namespace PROCERGS\LoginCidadao\NotificationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PROCERGS\LoginCidadao\NotificationBundle\Form\BroadcastType;
use Symfony\Component\HttpFoundation\Request;

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
            $url = $this->generateUrl('lc_notification_broadcast_new_placeholder',
              array('broadcastId' => $broadcast->getId()));
            return $this->redirect($url);
        }

        return array('form' => $form->createView(), 'clientId' => $clientId);
    }

    /**
     * @Route("/notifications/broadcasts/{broadcastId}/placeholders", name="lc_notification_broadcast_view_placeholder")
     * @Template()
     */
    public function viewPlaceholderAction(Request $request, $broadcastId)
    {
        $broadcast = $this->getDoctrine()->getRepository('PROCERGSLoginCidadaoNotificationBundle:Broadcast')
            ->find($broadcastId);

        $category = $broadcast->getCategory();
        $template = $category->getHtmlTemplate();

        $placeholders = $category->getPlaceholders();

        return compact('template', 'placeholders');
    }

}
