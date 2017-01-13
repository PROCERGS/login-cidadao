<?php

namespace PROCERGS\LoginCidadao\AccountingBundle\Controller;

use LoginCidadao\OAuthBundle\Entity\AccessTokenRepository;
use LoginCidadao\OAuthBundle\Entity\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/accounting", name="lc_accounting_data")
     * @Template()
     */
    public function indexAction()
    {
        /** @var AccessTokenRepository $accessTokens */
        $accessTokens = $this->getDoctrine()->getRepository('LoginCidadaoOAuthBundle:AccessToken');

        /** @var ClientRepository $clientsRepo */
        $clientsRepo = $this->getDoctrine()->getRepository('LoginCidadaoOAuthBundle:Client');

        $start = new \DateTime('-1 month');
        $end = new \DateTime();
        $data = $accessTokens->getAccounting($start, $end);

        $clientIds = array_column($data, 'id');
        $clients = [];

        foreach ($clientsRepo->findBy(['id' => $clientIds]) as $client) {
            $clients[$client->getId()] = $client;
        }

        $report = [];
        foreach ($data as $usage) {
            $report[] = [
                'client' => $clients[$usage['id']],
                'access_tokens' => $usage['access_tokens'],
            ];
        }

        return compact('report');
    }
}
