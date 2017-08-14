<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\AccountingBundle;

use PROCERGS\LoginCidadao\AccountingBundle\Entity\ProcergsLink;

class GcsInterface
{
    /** @var string */
    private $interfaceName;

    /** @var \DateTime */
    private $start;

    private $procergsSystems = [];
    private $externalSystems = [];
    private $invalidSystems = [];

    /**
     * GcsInterface constructor.
     * @param string $interfaceName
     * @param \DateTime $start
     */
    public function __construct($interfaceName, \DateTime $start)
    {
        $this->interfaceName = $interfaceName;
        $this->start = $start;
    }

    public function addClient($client)
    {
        switch ($client['system_type']) {
            case ProcergsLink::TYPE_EXTERNAL:
                $this->countExternalSystem($client);
                continue;
            case ProcergsLink::TYPE_INTERNAL:
                $this->countProcergsSystem($client);
                continue;
            default:
                $this->registerInvalidEntry($client);
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
        return $this->getStaticOwnerAndInitialsBody($this->externalSystems, 'EXTERNAL', 'EXTERNAL');
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

    private function countProcergsSystem($client)
    {
        $procergsInitials = $client['procergs_initials'];
        $totalUsage = $client['access_tokens'] + $client['api_usage'];

        if (count($procergsInitials) !== 1) {
            $this->invalidSystems[] = $totalUsage;

            return;
        }

        $owners = implode(' ', $client['procergs_owner']);
        foreach ($procergsInitials as $initials) {
            $this->procergsSystems[$initials]['owner'] = $owners;
            if (array_key_exists('usage', $this->procergsSystems[$initials])) {
                $this->procergsSystems[$initials]['usage'] += $totalUsage;
            } else {
                $this->procergsSystems[$initials]['usage'] = $totalUsage;
            }
        }
    }

    private function countExternalSystem($client)
    {
        $totalUsage = $client['access_tokens'] + $client['api_usage'];
        $this->externalSystems[] = $totalUsage;
    }

    private function registerInvalidEntry($client)
    {
        $totalUsage = $client['access_tokens'] + $client['api_usage'];
        $invalidSystems[] = $totalUsage;
    }

    public function __toString()
    {
        return $this->getHeader().PHP_EOL.
            $this->getBody().PHP_EOL.
            $this->getTail();
    }
}
