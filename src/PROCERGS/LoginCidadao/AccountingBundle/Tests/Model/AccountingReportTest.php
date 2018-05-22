<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\AccountingBundle\Tests\Model;

use LoginCidadao\OAuthBundle\Entity\Client;
use PROCERGS\LoginCidadao\AccountingBundle\Entity\ProcergsLink;
use PROCERGS\LoginCidadao\AccountingBundle\Model\AccountingReport;
use PROCERGS\LoginCidadao\AccountingBundle\Service\SystemsRegistryService;

/**
 * @codeCoverageIgnore
 */
class AccountingReportTest extends \PHPUnit_Framework_TestCase
{
    public function testAddEntryNotLinked()
    {
        $client = (new Client())->setId(123);

        $report = $this->getAccountingReport();
        $report->addEntry($client, 111);
        $report->addEntry($client, null, 222);
        $entry = $report->getReport()[$client->getId()];

        $this->assertEquals($client, $entry->getClient());
        $this->assertEquals(333, $entry->getTotalUsage());
        $this->assertEquals(ProcergsLink::TYPE_INTERNAL, $entry->getSystemType());
        $this->assertContains('XPTO', $entry->getProcergsInitials());
        $this->assertContains('OWNER', $entry->getProcergsOwner());
    }

    public function testAddEntryLinked()
    {
        $client = (new Client())->setId(321);

        $report = $this->getAccountingReport();
        $report->addEntry($client, 111);
        $report->addEntry($client, null, 222);
        $entry = $report->getReport()[$client->getId()];

        $this->assertEquals(ProcergsLink::TYPE_EXTERNAL, $entry->getSystemType());
    }

    public function testSorting()
    {
        $client1 = (new Client())->setId(123);
        $client2 = (new Client())->setId(456);
        $client3 = (new Client())->setId(789);
        $client4 = (new Client())->setId(987);

        $report = $this->getAccountingReport();
        $report->addEntry($client1, 111);
        $report->addEntry($client2);
        $report->addEntry($client3, null, 222);
        $report->addEntry($client4, null, 222);

        $this->doTestSorting($report);
    }

    public function testSortingIgnoringInactive()
    {
        $client1 = (new Client())->setId(123);
        $client2 = (new Client())->setId(456);
        $client3 = (new Client())->setId(789);

        $report = $this->getAccountingReport();
        $report->addEntry($client1, 111);
        $report->addEntry($client2);
        $report->addEntry($client3, null, 222);

        $options = ['include_inactive' => false];
        $this->doTestSorting($report, $options);

        $entries = $report->getReport($options);
        $this->assertArrayNotHasKey($client2->getId(), $entries);
    }

    public function testInvalidOrder()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $report = $this->getAccountingReport();
        $report->getReport(['sort' => 'INVALID']);
    }

    public function testLazyLoadInitials()
    {
        $getInitialsCalls = 0;
        $getOwnerCalls = 0;

        $registryClass = 'PROCERGS\LoginCidadao\AccountingBundle\Service\SystemsRegistryService';
        $registry = $this->getMockBuilder($registryClass)->disableOriginalConstructor()->getMock();
        $registry->expects($this->exactly(2))->method('getSystemInitials')
            ->willReturnCallback(function () use (&$getInitialsCalls) {
                $getInitialsCalls++;

                return ['XPTO'];
            });
        $registry->expects($this->exactly(2))->method('getSystemOwners')
            ->willReturnCallback(function () use (&$getOwnerCalls) {
                $getOwnerCalls++;

                return ['OWNER'];
            });

        $client1 = (new Client())->setId(123);
        $client2 = (new Client())->setId(456);
        $client3 = (new Client())->setId(789);

        $report = $this->getAccountingReport($registry);
        $report->addEntry($client1, 10, 20, true);
        $report->addEntry($client2, 30, 40, true);
        $report->addEntry($client3, 0, 0, true);

        $report->getReport(['include_inactive' => false]);
        $this->assertEquals(2, $getInitialsCalls);
        $this->assertEquals(2, $getOwnerCalls);
    }

    private function doTestSorting(AccountingReport $report, array $options = [])
    {
        $options['sort'] = AccountingReport::SORT_ORDER_ASC;
        $asc = $report->getReport($options);

        $lastUsage = -1;
        foreach ($asc as $entry) {
            $this->assertGreaterThanOrEqual($lastUsage, $entry->getTotalUsage());
            $lastUsage = $entry->getTotalUsage();
        }

        $options['sort'] = AccountingReport::SORT_ORDER_DESC;
        $desc = $report->getReport($options);
        $lastUsage = 9999;
        foreach ($desc as $entry) {
            $this->assertLessThanOrEqual($lastUsage, $entry->getTotalUsage());
            $lastUsage = $entry->getTotalUsage();
        }
    }

    private function getAccountingReport($registry = null)
    {
        if ($registry === null) {
            $registryClass = 'PROCERGS\LoginCidadao\AccountingBundle\Service\SystemsRegistryService';
            /** @var SystemsRegistryService|\PHPUnit_Framework_MockObject_MockObject $registry */
            $registry = $this->getMockBuilder($registryClass)->disableOriginalConstructor()->getMock();
            $registry->expects($this->any())->method('getSystemInitials')->willReturn(['XPTO']);
            $registry->expects($this->any())->method('getSystemOwners')->willReturn(['OWNER']);
        }

        $linked = [
            321 => (new ProcergsLink())
                ->setSystemType(ProcergsLink::TYPE_EXTERNAL)
                ->setClient((new Client())->setId(321)),
        ];

        return new AccountingReport($registry, $linked, new \DateTime());
    }
}
