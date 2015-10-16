<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Controller;

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

        $clients = $em->getRepository('PROCERGSOAuthBundle:Client');
        $client  = $clients->find($clientId);
        $user    = $this->getUser();

        $authorization = $this->getDoctrine()
            ->getRepository('PROCERGSLoginCidadaoCoreBundle:Authorization')
            ->findOneBy(array('person' => $user, 'client' => $client));

        $scopes = empty($authorization) ? array() : $authorization->getScope();

        $form = $this->createForm('procergs_revoke_authorization',
                array('client_id' => $clientId))->createView();

        return compact('user', 'client', 'scopes', 'form');
    }
}
