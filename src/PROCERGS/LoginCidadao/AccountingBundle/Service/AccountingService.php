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
        $start->setTime(0, 0, 0);
        $end->setTime(0, 0, 0);

        $data = $this->clientRepository->getAccessTokenAccounting($start, $end);
        $actionLog = $this->clientRepository->getActionLogAccounting($start, $end);

        $clientIds = array_merge(array_column($data, 'id'), array_column($actionLog, 'id'));

        $clients = [];
        /** @var Client $client */
        foreach ($this->clientRepository->findBy(['id' => $clientIds]) as $client) {
            $clients[$client->getId()] = $client;
        }

        $linked = $this->systemsRegistry->fetchLinked($clients, $this->procergsLinkRepository);

        $report = [];
        foreach ($data as $usage) {
            /** @var \LoginCidadao\OAuthBundle\Entity\Client $client */
            $client = $clients[$usage['id']];
            $report = $this->addReportEntry($report, $client, $linked, $usage['access_tokens'], null);
        }

        foreach ($actionLog as $action) {
            /** @var \LoginCidadao\OAuthBundle\Entity\Client $client */
            $client = $clients[$action['id']];
            $report = $this->addReportEntry($report, $client, $linked, null, $action['api_usage']);
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
     * Remove services that didn't use the API.
     *
     * @param array $data
     * @return array
     */
    public function filterOutInactive(array $data)
    {
        return array_filter($data, function ($client) {
            return $client['access_tokens'] + $client['api_usage'] > 0;
        });
    }

    /**
     * @param array $report
     * @param Client $client
     * @param array $linked
     * @param int|null $accessTokens
     * @param int|null $apiUsage
     * @return array
     */
    private function addReportEntry(
        array $report,
        Client $client,
        array $linked,
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
            $initials = $this->systemsRegistry->getSystemInitials($client);
            $owners = $this->systemsRegistry->getSystemOwners($client);
            if (array_key_exists($clientId, $linked)) {
                $systemType = $linked[$clientId]->getSystemType();
            } else {
                // If there is no known link we assume it's an Internal system
                // If this assumption is false then an alarm will go off to alert the accounting team to fix it
                $systemType = ProcergsLink::TYPE_INTERNAL;

                // Otherwise we could try to assert the type from the URLs we know
                //$systemType = $this->systemsRegistry->getTypeFromUrl($client);
            }
            $report[$clientId] = [
                'client' => $client,
                'procergs_initials' => $initials,
                'procergs_owner' => $owners,
                'system_type' => $systemType,
                'access_tokens' => $accessTokens ?: 0,
                'api_usage' => $apiUsage ?: 0,
            ];
        }

        return $report;
    }
}
