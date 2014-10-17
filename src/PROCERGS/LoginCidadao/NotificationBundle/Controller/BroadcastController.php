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
        return array();
    }

    /**
     * @Route("/clients/{clientId}/broadcasts/new", name="lc_notification_broadcast_new")
     * @Template()
     */
    public function newAction(Request $request, $clientId)
    {
        $form = $this->createForm(new BroadcastType($this->getUser(), $clientId));

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($form->getData());
            $em->flush();
            return $this->redirect($this->generateUrl('lc_notification_broadcast_list'));
        }

        return array('form' => $form->createView());
    }

}
