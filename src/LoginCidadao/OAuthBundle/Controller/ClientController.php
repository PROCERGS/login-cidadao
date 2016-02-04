<?php

namespace LoginCidadao\OAuthBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use LoginCidadao\OAuthBundle\Entity\Client;

class ClientController extends Controller
{
    /**
     * @Route("/auth/initClient", name="lc_oauth_init")
     * @Template()
     */
    public function initClientAction()
    {
        $clientManager = $this->get('fos_oauth_server.client_manager');
        
        $client = $clientManager->findClientBy(array('name' => "VPR"));
        if ($client instanceof Client) {
            $client->setAllowedGrantTypes(array('authorization_code'));
        } else {
            $client = $clientManager->createClient();
            $client->setRedirectUris(array('http://vpr.des.dona.to'));
            $client->setAllowedGrantTypes(array('authorization_code'));
            $client->setName("VPR");
            $client->setDescription("Votação de Prioridades do RS");
        }
        $clientManager->updateClient($client);
        
        die("ok");
    }
    
    /**
     * @Route("/auth/getPub/{id}", name="lc_oauth_getid")
     */
    public function getPublicIdAction($id)
    {
        $clientManager = $this->get('fos_oauth_server.client_manager');
        $client = $clientManager->findClientBy(array('id' => $id));
        die($client->getPublicId());
        
    }

}
