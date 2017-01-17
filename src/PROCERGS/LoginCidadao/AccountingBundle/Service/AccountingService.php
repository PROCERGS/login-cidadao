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
        $data = $this->accessTokenRepository->getAccounting($start, $end);

        $clientIds = array_column($data, 'id');

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
            if (array_key_exists($client->getId(), $knownInitials)) {
                $sisInfo = [$knownInitials[$client->getId()]->getSystemCode()];
            } else {
                $sisInfo = $this->systemsRegistry->getSystemInitials($client);
            }
            $report[] = [
                'client' => $client,
                'procergs' => $sisInfo,
                'access_tokens' => $usage['access_tokens'],
            ];
        }

        return array_map(
            function ($value) {
                /** @var \LoginCidadao\OAuthBundle\Entity\Client $client */
                $client = $value['client'];
                $value['client'] = [
                    'client_id' => $client->getPublicId(),
                    'name' => $client->getName(),
                    'contacts' => $client->getMetadata()->getContacts(),
                ];
                $value['redirect_uris'] = $client->getRedirectUris();

                return $value;
            },
            $report
        );
    }
}