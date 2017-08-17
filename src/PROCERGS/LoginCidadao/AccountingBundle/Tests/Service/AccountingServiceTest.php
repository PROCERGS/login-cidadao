<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\AccountingBundle\Tests\Service;

use LoginCidadao\OAuthBundle\Entity\Client;
use PROCERGS\LoginCidadao\AccountingBundle\Entity\ProcergsLink;
use PROCERGS\LoginCidadao\AccountingBundle\Service\AccountingService;

class AccountingServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testGetAccounting()
    {
        $client1 = (new Client())->setId(1);
        $client2 = (new Client())->setId(2);

        $clients = [
            $client1,
            $client2,
        ];

        $clientRepo = $this->getClientRepo();
        $clientRepo->expects($this->once())->method('getAccessTokenAccounting')->willReturn([
            ['id' => 1, 'access_tokens' => 111],
            ['id' => 2, 'access_tokens' => 111],
        ]);
        $clientRepo->expects($this->once())->method('getActionLogAccounting')->willReturn([
            ['id' => 1, 'api_usage' => 222],
            ['id' => 2, 'api_usage' => 222],
        ]);
        $clientRepo->expects($this->once())->method('findBy')
            ->willReturnCallback(function ($criteria) use ($clients) {
                $this->assertArrayHasKey('id', $criteria);
                $ids = $criteria['id'];
                $this->assertContains(1, $ids);
                $this->assertContains(2, $ids);
                $this->assertCount(2, $ids);

                return $clients;
            });

        $registry = $this->getRegistry();
        $registry->expects($this->once())->method('fetchLinked')
            ->willReturn([]);

        $service = $this->getAccountingService($registry, $clientRepo);
        $report = $service->getAccounting(new \DateTime(), new \DateTime());

        $this->assertInstanceOf('PROCERGS\LoginCidadao\AccountingBundle\Model\AccountingReport', $report);
        $this->assertCount(2, $report->getReport());
        foreach ($report->getReport() as $entry) {
            $this->assertContains($entry->getClient(), $clients);
            $this->assertEquals(333, $entry->getTotalUsage());
        }
    }

    public function testGetEmptyAccounting()
    {
        $clientRepo = $this->getClientRepo();
        $clientRepo->expects($this->once())->method('getAccessTokenAccounting')->willReturn([]);
        $clientRepo->expects($this->once())->method('getActionLogAccounting')->willReturn([]);
        $clientRepo->expects($this->once())->method('findBy')->willReturn([]);

        $registry = $this->getRegistry();
        $registry->expects($this->once())->method('fetchLinked')->willReturn([]);

        $service = $this->getAccountingService($registry, $clientRepo);
        $report = $service->getAccounting(new \DateTime(), new \DateTime());

        $this->assertInstanceOf('PROCERGS\LoginCidadao\AccountingBundle\Model\AccountingReport', $report);
        $this->assertEmpty($report->getReport());
    }

    public function testGetGcsInterface()
    {
        $interfaceName = 'My_Interface';

        $client1 = (new Client())->setId(1);
        $client2 = (new Client())->setId(2);

        $clients = [
            $client1,
            $client2,
        ];

        $clientRepo = $this->getClientRepo();
        $clientRepo->expects($this->once())->method('getAccessTokenAccounting')->willReturn([
            ['id' => 1, 'access_tokens' => 111],
            ['id' => 2, 'access_tokens' => 111],
        ]);
        $clientRepo->expects($this->once())->method('getActionLogAccounting')->willReturn([
            ['id' => 1, 'api_usage' => 222],
            ['id' => 2, 'api_usage' => 222],
        ]);
        $clientRepo->expects($this->once())->method('findBy')->willReturn($clients);

        $registry = $this->getRegistry();
        $registry->expects($this->once())->method('fetchLinked')
            ->willReturn([
                $client1->getId() => (new ProcergsLink())
                    ->setClient($client1)
                    ->setSystemType(ProcergsLink::TYPE_INTERNAL),
            ]);

        $service = $this->getAccountingService($registry, $clientRepo);
        $report = $service->getGcsInterface($interfaceName, new \DateTime(), new \DateTime());
        $lines = explode(PHP_EOL, $report);

        $this->assertContains($interfaceName, $lines[0]);
        $this->assertEquals('9;2', end($lines));
    }

    private function getAccountingService(
        $registry,
        $clientRepo,
        $linkRepo = null
    ) {
        if (!$linkRepo) {
            $linkRepo = $this->getMockBuilder('PROCERGS\LoginCidadao\AccountingBundle\Entity\ProcergsLinkRepository')
                ->disableOriginalConstructor()->getMock();
        }

        return new AccountingService($registry, $clientRepo, $linkRepo);
    }

    private function getRegistry()
    {
        return $this->getMockBuilder('PROCERGS\LoginCidadao\AccountingBundle\Service\SystemsRegistryService')
            ->disableOriginalConstructor()->getMock();
    }

    private function getClientRepo()
    {
        return $this->getRepo('LoginCidadao\OAuthBundle\Entity\ClientRepository');
    }

    private function getRepo($className)
    {
        $repo = $this->getMockBuilder($className)
            ->disableOriginalConstructor()->getMock();

        return $repo;
    }
}
