<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\StatsBundle\Tests\Handler;


use LoginCidadao\StatsBundle\Entity\Statistics;
use LoginCidadao\StatsBundle\Entity\StatisticsRepository;
use LoginCidadao\StatsBundle\Handler\StatsHandler;
use PHPUnit\Framework\TestCase;

class StatsHandlerTest extends TestCase
{
    public function testHandler()
    {
        $index = 'index.name';
        $key = 'key';
        $afterDate = new \DateTime();
        $stats = [new Statistics()];

        /** @var \PHPUnit_Framework_MockObject_MockObject|StatisticsRepository $repo */
        $repo = $this->getRepository();
        /** @scrutinizer ignore-call */
        $repo->expects($this->once())->method('findStatsByIndexKeyDate')->with($index, $key, $afterDate)
            ->willReturn($stats);

        $handler = new StatsHandler($repo);
        $this->assertSame($stats, $handler->get($index, $key, $afterDate));
    }

    public function testGetIndexed()
    {
        $index = 'index.name';
        $key = 'key';
        $days = 5;
        $response = [new Statistics()];

        /** @var \PHPUnit_Framework_MockObject_MockObject|StatisticsRepository $repo */
        $repo = $this->getRepository();
        /** @scrutinizer ignore-call */
        $repo->expects($this->once())->method('findIndexedStatsByIndexKeyDays')->with($index, $key, $days)
            ->willReturn($response);

        $handler = new StatsHandler($repo);
        $this->assertSame($response, $handler->getIndexed($index, $key, $days));
    }

    public function testGetIndexedUniqueDate()
    {
        $index = 'index.name';
        $key = 'key';
        $afterDate = new \DateTime();
        $response = [new Statistics()];

        /** @var \PHPUnit_Framework_MockObject_MockObject|StatisticsRepository $repo */
        $repo = $this->getRepository();
        /** @scrutinizer ignore-call */
        $repo->expects($this->once())->method('findIndexedUniqueStatsByIndexKeyDate')->with($index, $key, $afterDate)
            ->willReturn($response);

        $handler = new StatsHandler($repo);
        $this->assertSame($response, $handler->getIndexedUniqueDate($index, $key, $afterDate));
    }

    public function testGetIndexedUniqueLastDays()
    {
        $index = 'index.name';
        $key = 'key';
        $days = 5;
        $response = [new Statistics()];

        /** @var \PHPUnit_Framework_MockObject_MockObject|StatisticsRepository $repo */
        $repo = $this->getRepository();
        /** @scrutinizer ignore-call */
        $repo->expects($this->once())->method('findIndexedUniqueStatsByIndexKeyDays')->with($index, $key, $days)
            ->willReturn($response);

        $handler = new StatsHandler($repo);
        $this->assertSame($response, $handler->getIndexedUniqueLastDays($index, $key, $days));
    }

    public function testGetOneByDate()
    {
        $index = 'index.name';
        $key = 'key';
        $date = new \DateTime();
        $response = new Statistics();

        /** @var \PHPUnit_Framework_MockObject_MockObject|StatisticsRepository $repo */
        $repo = $this->getRepository();
        /** @scrutinizer ignore-call */
        $repo->expects($this->once())->method('findOneBy')->with([
            'timestamp' => $date,
            'index' => $index,
            'key' => $key,
        ])->willReturn($response);

        $handler = new StatsHandler($repo);
        $this->assertSame($response, $handler->getOneByDate($date, $index, $key));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|StatisticsRepository
     */
    private function getRepository()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|StatisticsRepository $repo */
        $repo = $this->getMockBuilder('LoginCidadao\StatsBundle\Entity\StatisticsRepository')
            ->disableOriginalConstructor()->getMock();

        return $repo;
    }
}
