<?php

namespace PROCERGS\LoginCidadao\AccountingBundle\Controller;

use LoginCidadao\OAuthBundle\Entity\AccessTokenRepository;
use LoginCidadao\OAuthBundle\Entity\ClientRepository;
use PROCERGS\LoginCidadao\AccountingBundle\Service\SystemsRegistryService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends Controller
{
    /**
     * @Route("/accounting", name="lc_accounting_data")
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

        /** @var SystemsRegistryService $systemsService */
        $systemsRegistry = $this->get('procergs.lc.procergs_systems.api');

        $report = [];
        foreach ($data as $usage) {
            /** @var \LoginCidadao\OAuthBundle\Entity\Client $client */
            $client = $clients[$usage['id']];
            $sisInfo = $systemsRegistry->getSystemInitials($client->getRedirectUris());
            $report[] = [
                'client' => $client,
                'procergs' => $sisInfo,
                'access_tokens' => $usage['access_tokens'],
            ];
        }

        return new JsonResponse(
            array_map(
                function ($value) {
                    /** @var \LoginCidadao\OAuthBundle\Entity\Client $client */
                    $client = $value['client'];
                    $value['client'] = $client->getName();
                    $value['redirect_uris'] = $client->getRedirectUris();

                    return $value;
                },
                $report
            )
        );
    }
}
