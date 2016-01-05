<?php

namespace LoginCidadao\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class ClientController extends Controller
{

    /**
     * @Route("/client/view/{clientId}", name="lc_app_details")
     * @Template()
     */
    public function viewAction($clientId)
    {
        $em = $this->getDoctrine()->getManager();

        $clients = $em->getRepository('LoginCidadaoOAuthBundle:Client');
        $client  = $clients->find($clientId);
        $user    = $this->getUser();

        $authorization = $this->getDoctrine()
            ->getRepository('LoginCidadaoCoreBundle:Authorization')
            ->findOneBy(array('person' => $user, 'client' => $client));

        $scopes = empty($authorization) ? array() : $authorization->getScope();

        $form = $this->createForm('lc_revoke_authorization',
                array('client_id' => $clientId))->createView();

        return compact('user', 'client', 'scopes', 'form');
    }
}
