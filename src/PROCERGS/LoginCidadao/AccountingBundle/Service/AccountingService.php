<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\AccountingBundle\Service;

use LoginCidadao\OAuthBundle\Entity\AccessTokenRepository;
use LoginCidadao\OAuthBundle\Entity\Client;
use LoginCidadao\OAuthBundle\Entity\ClientRepository;
use PROCERGS\LoginCidadao\AccountingBundle\Entity\ProcergsLink;
use PROCERGS\LoginCidadao\AccountingBundle\Entity\ProcergsLinkRepository;

class AccountingService
{
    /** @var SystemsRegistryService */
    private $systemsRegistry;

    /** @var AccessTokenRepository */
    private $accessTokenRepository;

    /** @var ClientRepository */
    private $clientRepository;

    /** @var ProcergsLinkRepository */
    private $procergsLinkRepository;

    /**
     * AccountingService constructor.
     * @param SystemsRegistryService $systemsRegistry
     * @param AccessTokenRepository $accessTokenRepository
     * @param ClientRepository $clientRepository
     * @param ProcergsLinkRepository $procergsLinkRepository
     */
    public function __construct(
        SystemsRegistryService $systemsRegistry,
        AccessTokenRepository $accessTokenRepository,
        ClientRepository $clientRepository,
        ProcergsLinkRepository $procergsLinkRepository
    ) {
        $this->systemsRegistry = $systemsRegistry;
        $this->accessTokenRepository = $accessTokenRepository;
        $this->clientRepository = $clientRepository;
        $this->procergsLinkRepository = $procergsLinkRepository;
    }

    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @return array
     */
    public function getAccounting(\DateTime $start, \DateTime $end)
    {
        $data = $this->clientRepository->getAccessTokenAccounting($start, $end);
        $actionLog = $this->clientRepository->getActionLogAccounting($start, $end);

        $clientIds = array_merge(array_column($data, 'id'), array_column($actionLog, 'id'));

        $clients = [];
        /** @var Client $client */
        foreach ($this->clientRepository->findBy(['id' => $clientIds]) as $client) {
            $clients[$client->getId()] = $client;
        }

        $knownInitials = $this->systemsRegistry->fetchKnownInitials($clients, $this->procergsLinkRepository);

        $report = [];
        foreach ($data as $usage) {
            /** @var \LoginCidadao\OAuthBundle\Entity\Client $client */
            $client = $clients[$usage['id']];
            $report = $this->addReportEntry($report, $client, $knownInitials, $usage['access_tokens'], null);
        }

        foreach ($actionLog as $action) {
            /** @var \LoginCidadao\OAuthBundle\Entity\Client $client */
            $client = $clients[$action['id']];
            $report = $this->addReportEntry($report, $client, $knownInitials, null, $action['api_usage']);
        }

        return array_map(
            function ($value) {
                /** @var \LoginCidadao\OAuthBundle\Entity\Client $client */
                $client = $value['client'];
                $value['client'] = [
                    'client_id' => $client->getPublicId(),
                    'name' => $client->getName(),
                    'contacts' => $client->getContacts(),
                ];
                $value['redirect_uris'] = $client->getRedirectUris();

                return $value;
            },
            $report
        );
    }

    /**
     * @param array $report
     * @param Client $client
     * @param ProcergsLink[] $knownInitials
     * @param int|null $accessTokens
     * @param int|null $apiUsage
     * @return array
     */
    private function addReportEntry(
        array $report,
        Client $client,
        array $knownInitials,
        $accessTokens = null,
        $apiUsage = null
    ) {
        $clientId = $client->getId();

        if (array_key_exists($clientId, $report)) {
            if ($accessTokens) {
                $report[$clientId]['access_tokens'] = $accessTokens;
            }
            if ($apiUsage) {
                $report[$clientId]['api_usage'] = $apiUsage;
            }
        } else {
            if (array_key_exists($client->getId(), $knownInitials)) {
                $sisInfo = [$knownInitials[$client->getId()]->getSystemCode()];
            } else {
                $sisInfo = $this->systemsRegistry->getSystemInitials($client);
            }
            $report[$clientId] = [
                'client' => $client,
                'procergs' => $sisInfo,
                'access_tokens' => $accessTokens ?: 0,
                'api_usage' => $apiUsage ?: 0,
            ];
        }

        return $report;
    }
}
