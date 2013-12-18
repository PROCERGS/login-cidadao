<?php

namespace PROCERGS\OAuthBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PROCERGS\OAuthBundle\Entity\Client;

class ClientController extends Controller
{
    /**
     * @Route("/initClient")
     * @Template()
     */
    public function initClientAction()
    {
        $clientManager = $this->get('fos_oauth_server.client_manager.default');
        
        $client = $clientManager->findClientBy(array('name' => "VPR"));
        if ($client instanceof Client) {
            $client->setAllowedGrantTypes(array('token', 'authorization_code', 'name', 'cpf'));
        } else {
            $client = $clientManager->createClient();
            $client->setRedirectUris(array('http://vpr.des.dona.to'));
            $client->setAllowedGrantTypes(array('token', 'authorization_code'));
            $client->setName("VPR");
            $client->setDescription("Votação de Prioridades do RS");
        }
        $clientManager->updateClient($client);
        
    }
    
    /**
     * @Route("/getPub/{id}")
     */
    public function getPublicIdAction($id)
    {
        $clientManager = $this->get('fos_oauth_server.client_manager.default');
        $client = $clientManager->findClientBy(array('id' => $id));
        die($client->getPublicId());
        
    }

}
