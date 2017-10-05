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

use LoginCidadao\OAuthBundle\Entity\Client;
use LoginCidadao\OAuthBundle\Entity\ClientRepository;
use PROCERGS\LoginCidadao\AccountingBundle\Entity\ProcergsLinkRepository;
use PROCERGS\LoginCidadao\AccountingBundle\Model\AccountingReport;
use PROCERGS\LoginCidadao\AccountingBundle\Model\GcsInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class AccountingService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var SystemsRegistryService */
    private $systemsRegistry;

    /** @var ClientRepository */
    private $clientRepository;

    /** @var ProcergsLinkRepository */
    private $procergsLinkRepository;

    /**
     * AccountingService constructor.
     * @param SystemsRegistryService $systemsRegistry
     * @param ClientRepository $clientRepository
     * @param ProcergsLinkRepository $procergsLinkRepository
     */
    public function __construct(
        SystemsRegistryService $systemsRegistry,
        ClientRepository $clientRepository,
        ProcergsLinkRepository $procergsLinkRepository
    ) {
        $this->systemsRegistry = $systemsRegistry;
        $this->clientRepository = $clientRepository;
        $this->procergsLinkRepository = $procergsLinkRepository;
    }

    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @return AccountingReport
     */
    public function getAccounting(\DateTime $start, \DateTime $end)
    {
        $this->logger->info("Getting accounting between {$start->format('c')} and {$end->format('c')}");
        $start->setTime(0, 0, 0);
        $end->setTime(0, 0, 0);

        $data = $this->clientRepository->getAccessTokenAccounting($start, $end);
        $actionLog = $this->clientRepository->getActionLogAccounting($start, $end);

        $this->logger->info("Loaded accounting data");

        $clientIds = array_unique(array_merge(
            array_column($data, 'id'),
            array_column($actionLog, 'id')
        ));

        $clients = [];
        /** @var Client $client */
        foreach ($this->clientRepository->findBy(['id' => $clientIds]) as $client) {
            $clients[$client->getId()] = $client;
        }

        $this->logger->info("Loading linked clients...");
        $linked = $this->systemsRegistry->fetchLinked($clients, $this->procergsLinkRepository);

        $this->logger->info("Preparing AccountingReport object...");
        $report = new AccountingReport($this->systemsRegistry, $linked);
        foreach ($data as $usage) {
            /** @var \LoginCidadao\OAuthBundle\Entity\Client $client */
            $report->addEntry($clients[$usage['id']], $usage['access_tokens'], null);
        }

        foreach ($actionLog as $action) {
            /** @var \LoginCidadao\OAuthBundle\Entity\Client $client */
            $report->addEntry($clients[$action['id']], null, $action['api_usage']);
        }
        $this->logger->info("AccountingReport object ready.");

        return $report;
    }

    public function getGcsInterface($interfaceName, \DateTime $start, \DateTime $end)
    {
        $data = $this->getAccounting($start, $end)->getReport(['include_inactive' => false]);

        $this->logger->info("Preparing GCS Interface...");
        $gcsInterface = new GcsInterface($interfaceName, $start, ['ignore_externals' => true]);

        foreach ($data as $client) {
            $this->logger->info(
                "Including {$client->getClient()->getPublicId()} into GCS Interface...",
                ['entry' => $client]
            );
            $gcsInterface->addClient($client);
        }
        $this->logger->info("GCS Interface object is ready.");

        $response = $gcsInterface->__toString();
        $this->logger->info("Resulting GCS Interface length: ".count($response), ['response' => $response]);

        return $response;
    }
}
