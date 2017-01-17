<?php

namespace PROCERGS\LoginCidadao\AccountingBundle\Controller;

use LoginCidadao\OAuthBundle\Entity\AccessTokenRepository;
use LoginCidadao\OAuthBundle\Entity\ClientRepository;
use PROCERGS\LoginCidadao\AccountingBundle\Entity\ProcergsLink;
use PROCERGS\LoginCidadao\AccountingBundle\Service\SystemsRegistryService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends Controller
{
    /**
     * @Route("/accounting.{_format}", name="lc_accounting_data", defaults={"_format": "json"})
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

        /** @var SystemsRegistryService $systemsRegistry */
        $systemsRegistry = $this->get('procergs.lc.procergs_systems.api');

        $linkRepo = $this->getDoctrine()->getRepository('PROCERGSLoginCidadaoAccountingBundle:ProcergsLink');
        $knownInitials = $systemsRegistry->fetchKnownInitials($clients, $linkRepo);

        $report = [];
        foreach ($data as $usage) {
            /** @var \LoginCidadao\OAuthBundle\Entity\Client $client */
            $client = $clients[$usage['id']];
            if (array_key_exists($client->getId(), $knownInitials)) {
                $sisInfo = [$knownInitials[$client->getId()]->getSystemCode()];
            } else {
                $sisInfo = $systemsRegistry->getSystemInitials($client);
            }
            $report[] = [
                'client' => $client,
                'procergs' => $sisInfo,
                'access_tokens' => $usage['access_tokens'],
            ];
        }

        $response = array_map(
            function ($value) {
                /** @var \LoginCidadao\OAuthBundle\Entity\Client $client */
                $client = $value['client'];
                $value['client'] = [
                    'name' => $client->getName(),
                    'contacts' => $client->getMetadata()->getContacts(),
                ];
                $value['redirect_uris'] = $client->getRedirectUris();

                return $value;
            },
            $report
        );

        return new JsonResponse($response);
    }
}
