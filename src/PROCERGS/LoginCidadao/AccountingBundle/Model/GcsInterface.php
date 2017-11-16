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

use PROCERGS\LoginCidadao\AccountingBundle\Entity\ProcergsLink;

class GcsInterface
{
    /** @var string */
    private $interfaceName;

    /** @var \DateTime */
    private $start;

    /** @var array */
    private $config;

    private $procergsSystems = [];
    private $externalSystems = [];
    private $invalidSystems = [];

    /**
     * GcsInterface constructor.
     * @param string $interfaceName
     * @param \DateTime $start
     * @param array $config
     */
    public function __construct($interfaceName, \DateTime $start, array $config = [])
    {
        $this->interfaceName = $interfaceName;
        $this->start = $start;

        $this->config = array_merge([
            'ignore_externals' => false,
            'external_label' => 'EXTERNAL',
        ], $config);
    }

    public function addClient(AccountingReportEntry $reportEntry)
    {
        switch ($reportEntry->getSystemType()) {
            case ProcergsLink::TYPE_EXTERNAL:
                $this->externalSystems[] = $reportEntry->getTotalUsage();
                continue;
            case ProcergsLink::TYPE_INTERNAL:
                $this->countProcergsSystem($reportEntry);
                continue;
            default:
                $this->registerInvalidEntry($reportEntry);
                continue;
        }
    }

    public function getHeader()
    {
        return implode(';', [
            '1',
            $this->interfaceName,
            $this->start->format('mY'),
            (new \DateTime())->format('dmY'),
        ]);
    }

    public function getBody()
    {
        $body = array_merge(
            $this->getProcergsSystemsBody(),
            $this->getExternalSystemsBody(),
            $this->getInvalidSystemsBody()
        );

        return implode("\n", $body);
    }

    public function getTail()
    {
        $body = array_merge(
            $this->getProcergsSystemsBody(),
            $this->getExternalSystemsBody(),
            $this->getInvalidSystemsBody()
        );

        return '9;'.count($body);
    }

    private function getProcergsSystemsBody()
    {
        $body = [];
        foreach ($this->procergsSystems as $initials => $sysInfo) {
            $body[] = implode(';', ['2', $sysInfo['owner'], $initials, $sysInfo['usage']]);
        }

        return $body;
    }

    private function getExternalSystemsBody()
    {
        if ($this->config['ignore_externals']) {
            return [];
        }

        return $this->getStaticOwnerAndInitialsBody($this->externalSystems, $this->config['external_label'],
            $this->config['external_label']);
    }

    private function getInvalidSystemsBody()
    {
        return $this->getStaticOwnerAndInitialsBody($this->invalidSystems);
    }

    private function getStaticOwnerAndInitialsBody($entries, $owner = '', $initials = '')
    {
        $body = [];
        foreach ($entries as $usage) {
            $body[] = implode(';', ['2', $owner, $initials, $usage]);
        }

        return $body;
    }

    private function countProcergsSystem(AccountingReportEntry $reportEntry)
    {
        $procergsInitials = $reportEntry->getProcergsInitials();
        $totalUsage = $reportEntry->getTotalUsage();

        if (count($procergsInitials) !== 1) {
            $this->registerInvalidEntry($reportEntry);

            return;
        }

        $owners = implode(' ', $reportEntry->getProcergsOwner());
        foreach ($procergsInitials as $initials) {
            $this->procergsSystems[$initials]['owner'] = $owners;
            if (array_key_exists('usage', $this->procergsSystems[$initials])) {
                $this->procergsSystems[$initials]['usage'] += $totalUsage;
            } else {
                $this->procergsSystems[$initials]['usage'] = $totalUsage;
            }
        }
    }

    private function registerInvalidEntry(AccountingReportEntry $reportEntry)
    {
        $this->invalidSystems[] = $reportEntry->getTotalUsage();
    }

    public function __toString()
    {
        $header = $this->getHeader();
        $body = $this->getBody();
        $tail = $this->getTail();

        return $header.PHP_EOL.
            $body.PHP_EOL.
            $tail;
    }
}
