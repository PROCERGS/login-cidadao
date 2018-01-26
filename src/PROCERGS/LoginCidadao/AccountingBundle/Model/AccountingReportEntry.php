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

class AccountingReportEntry
{
    /** @var ClientInterface */
    private $client;

    /** @var string[] */
    private $procergsInitials;

    /** @var string[] */
    private $procergsOwner;

    /** @var string */
    private $systemType;

    /** @var int */
    private $accessTokens;

    /** @var int */
    private $apiUsage;

    /**
     * Register if this Entry was already queried in the Systems Registry.
     *
     * @var bool
     */
    private $queriedSystemsRegistry = false;

    /**
     * @return ClientInterface
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param ClientInterface $client
     * @return AccountingReportEntry
     */
    public function setClient($client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getProcergsInitials()
    {
        return $this->procergsInitials;
    }

    /**
     * @param string[] $procergsInitials
     * @return AccountingReportEntry
     */
    public function setProcergsInitials($procergsInitials)
    {
        $this->procergsInitials = $procergsInitials;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getProcergsOwner()
    {
        return $this->procergsOwner;
    }

    /**
     * @param string[] $procergsOwner
     * @return AccountingReportEntry
     */
    public function setProcergsOwner($procergsOwner)
    {
        $this->procergsOwner = $procergsOwner;

        return $this;
    }

    /**
     * @return string
     */
    public function getSystemType()
    {
        return $this->systemType;
    }

    /**
     * @param string $systemType
     * @return AccountingReportEntry
     */
    public function setSystemType($systemType)
    {
        $this->systemType = $systemType;

        return $this;
    }

    /**
     * @return int
     */
    public function getAccessTokens()
    {
        return $this->accessTokens;
    }

    /**
     * @param int $accessTokens
     * @return AccountingReportEntry
     */
    public function setAccessTokens($accessTokens)
    {
        $this->accessTokens = $accessTokens;

        return $this;
    }

    /**
     * @return int
     */
    public function getApiUsage()
    {
        return $this->apiUsage;
    }

    /**
     * @param int $apiUsage
     * @return AccountingReportEntry
     */
    public function setApiUsage($apiUsage)
    {
        $this->apiUsage = $apiUsage;

        return $this;
    }

    public function getTotalUsage()
    {
        return $this->getAccessTokens() + $this->getApiUsage();
    }

    /**
     * @return bool
     */
    public function isQueriedSystemsRegistry()
    {
        return $this->queriedSystemsRegistry;
    }

    /**
     * @param bool $queriedSystemsRegistry
     * @return AccountingReportEntry
     */
    public function setQueriedSystemsRegistry($queriedSystemsRegistry)
    {
        $this->queriedSystemsRegistry = $queriedSystemsRegistry;

        return $this;
    }
}
