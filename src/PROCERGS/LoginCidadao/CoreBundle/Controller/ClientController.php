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
        $client = $clients->find($clientId);
        $clientScopes = $client->getAllowedScopes();

        $user = $this->getUser();

        $clientScopes = $client->getAllowedScopes();

        $authorization = $this->getDoctrine()
                ->getRepository('PROCERGSLoginCidadaoCoreBundle:Authorization')
                ->findOneBy(array(
            'person' => $user,
            'client' => $client
        ));
        $userScopes = empty($authorization) ? array() : $authorization->getScope();
        $clientScopes = empty($clientScopes) ? array() : $clientScopes;

        $mergeAuth = array_merge($clientScopes, $userScopes);

        $scopes = array();
        foreach ($mergeAuth as $s) {
            $scopes[$s] = in_array($s, $userScopes) ? true : false;
        }

        $form = $this->createForm('procergs_revoke_authorization',
                array('client_id' => $clientId));
        $form = $form->createView();

        return compact('user', 'client', 'scopes', 'form');
    }

}
