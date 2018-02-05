<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\AccountingBundle\Model;

use LoginCidadao\OAuthBundle\Model\ClientInterface;
use PROCERGS\LoginCidadao\AccountingBundle\Entity\ProcergsLink;
use PROCERGS\LoginCidadao\AccountingBundle\Service\SystemsRegistryService;

class AccountingReport
{
    const SORT_ORDER_ASC = 'asc';
    const SORT_ORDER_DESC = 'desc';

    /** @var SystemsRegistryService */
    private $systemsRegistry;

    /** @var ProcergsLink[] */
    private $linked;

    /** @var AccountingReportEntry[] */
    private $report = [];

    /** @var \DateTime */
    private $activeAfter;

    /**
     * AccountingReport constructor.
     * @param SystemsRegistryService $systemsRegistry
     * @param array $linked
     * @param \DateTime $activeAfter
     */
    public function __construct(SystemsRegistryService $systemsRegistry, array $linked = [], \DateTime $activeAfter)
    {
        $this->systemsRegistry = $systemsRegistry;
        $this->linked = $linked;
        $this->activeAfter = $activeAfter;
    }

    public function addEntry(ClientInterface $client, $accessTokens = null, $apiUsage = null, $lazyLoad = false)
    {
        $this->createEntryIfNeeded($client, !$lazyLoad);
        $entry = $this->getEntry($client);

        if ($accessTokens) {
            $entry->setAccessTokens($accessTokens);
        }
        if ($apiUsage) {
            $entry->setApiUsage($apiUsage);
        }
    }

    public function getReport(array $options = [])
    {
        $report = $this->report;

        $options = array_merge([
            'include_inactive' => true,
            'sort' => null,
        ], $options);
        if (!$options['include_inactive']) {
            $report = array_filter($this->report, function (AccountingReportEntry $entry) {
                return $entry->getTotalUsage() > 0;
            });
        }

        if ($options['sort'] !== null) {
            $report = $this->sortReport($report, $options['sort']);
        }

        $systemsRegistry = $this->systemsRegistry;
        $report = array_map(function (AccountingReportEntry $entry) use ($systemsRegistry) {
            if (false === $entry->isQueriedSystemsRegistry()) {
                $client = $entry->getClient();
                $entry->setProcergsInitials($systemsRegistry->getSystemInitials($client, $this->activeAfter));
                $entry->setProcergsOwner($systemsRegistry->getSystemOwners($client, $this->activeAfter));
                $entry->setQueriedSystemsRegistry(true);
            }

            return $entry;
        }, $report);

        return $report;
    }

    private function createEntryIfNeeded(ClientInterface $client, $querySystemsRegistry = true)
    {
        $clientId = $client->getId();
        if (array_key_exists($clientId, $this->report)) {
            return;
        }

        $initials = null;
        $owners = null;
        if ($querySystemsRegistry) {
            $initials = $this->systemsRegistry->getSystemInitials($client, $this->activeAfter);
            $owners = $this->systemsRegistry->getSystemOwners($client, $this->activeAfter);
        }

        $this->report[$clientId] = (new AccountingReportEntry())
            ->setClient($client)
            ->setProcergsInitials($initials)
            ->setProcergsOwner($owners)
            ->setSystemType($this->getSystemType($clientId))
            ->setAccessTokens(0)
            ->setApiUsage(0)
            ->setQueriedSystemsRegistry($querySystemsRegistry);
    }

    private function getEntry(ClientInterface $client)
    {
        return $this->report[$client->getId()];
    }

    private function getSystemType($clientId)
    {
        if (array_key_exists($clientId, $this->linked)) {
            return $this->linked[$clientId]->getSystemType();
        }

        // If there is no known link we assume it's an Internal system
        // If this assumption is false then an alarm will go off to alert the accounting team to fix it
        return ProcergsLink::TYPE_INTERNAL;
    }

    private function sortReport($report, $order)
    {
        $order = strtolower(trim($order));
        switch ($order) {
            case self::SORT_ORDER_ASC:
                $order = -1;
                break;
            case self::SORT_ORDER_DESC:
                $order = 1;
                break;
            default:
                throw new \InvalidArgumentException("Invalid sorting order '{$order}'");
        }

        uasort($report, function (AccountingReportEntry $a, AccountingReportEntry $b) use ($order) {
            if ($a->getTotalUsage() === $b->getTotalUsage()) {
                return 0;
            }

            return ($a->getTotalUsage() < $b->getTotalUsage()) ? $order : $order * -1;
        });

        return $report;
    }
}
