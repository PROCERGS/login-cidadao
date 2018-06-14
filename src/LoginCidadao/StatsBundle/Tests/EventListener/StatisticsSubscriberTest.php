<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\StatsBundle\Tests\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use LoginCidadao\CoreBundle\Entity\Authorization;
use LoginCidadao\CoreBundle\Entity\Person;
use LoginCidadao\OAuthBundle\Entity\Client;
use LoginCidadao\StatsBundle\EventListener\StatisticsSubscriber;
use PHPUnit\Framework\TestCase;

class StatisticsSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents()
    {
        $this->assertSame(['postPersist', 'postRemove'], (new StatisticsSubscriber())->getSubscribedEvents());
    }

    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function testPostPersistNonAuthorization()
    {
        /** @var EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject $em */
        $em = $this->createMock('Doctrine\ORM\EntityManagerInterface');
        $em->expects($this->never())->method('flush');

        $args = new LifecycleEventArgs(new \stdClass(), $em);

        $subscriber = new StatisticsSubscriber();
        $subscriber->postPersist($args);
    }

    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function testPostPersist()
    {
        $person = new Person();
        $clientId = 'client666';
        $authorization = new Authorization();
        $authorization->setPerson($person);
        $authorization->setClient((new Client())->setId($clientId));

        $count = [
            ['qty' => 999],
        ];

        $repo = $this->getMockBuilder('LoginCidadao\OAuthBundle\Entity\ClientRepository')
            ->disableOriginalConstructor()->getMock();
        $repo->expects($this->exactly(2))
            ->method('getCountPerson')->with($person, $clientId)
            ->willReturn($count);

        $statsRepo = $this->getMockBuilder('LoginCidadao\StatsBundle\Entity\StatisticsRepository')
            ->disableOriginalConstructor()->getMock();
        $statsRepo->expects($this->once())
            ->method('findOneBy')->with($this->isType('array'))
            ->willReturnCallback(function ($where) use ($clientId) {
                $this->assertInstanceOf('\DateTime', $where['timestamp']);
                $this->assertSame('agg.client.users', $where['index']);
                $this->assertSame($clientId, $where['key']);
            });

        /** @var EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject $em */
        $em = $this->createMock('Doctrine\ORM\EntityManagerInterface');
        $em->expects($this->exactly(3))
            ->method('getRepository')
            ->willReturnMap([
                ['LoginCidadaoOAuthBundle:Client', $repo],
                ['LoginCidadaoStatsBundle:Statistics', $statsRepo],
            ]);

        $args = new LifecycleEventArgs($authorization, $em);

        $subscriber = new StatisticsSubscriber();
        $subscriber->postPersist($args);
    }

    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function testPostRemove()
    {
        $person = new Person();
        $clientId = 'client666';
        $authorization = new Authorization();
        $authorization->setPerson($person);
        $authorization->setClient((new Client())->setId($clientId));

        $count = [];

        $repo = $this->getMockBuilder('LoginCidadao\OAuthBundle\Entity\ClientRepository')
            ->disableOriginalConstructor()->getMock();
        $repo->expects($this->exactly(2))
            ->method('getCountPerson')->with($person, $clientId)
            ->willReturn($count);

        $statsRepo = $this->getMockBuilder('LoginCidadao\StatsBundle\Entity\StatisticsRepository')
            ->disableOriginalConstructor()->getMock();
        $statsRepo->expects($this->once())
            ->method('findOneBy')->with($this->isType('array'))
            ->willReturnCallback(function ($where) use ($clientId) {
                $this->assertInstanceOf('\DateTime', $where['timestamp']);
                $this->assertSame('agg.client.users', $where['index']);
                $this->assertSame($clientId, $where['key']);
            });

        /** @var EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject $em */
        $em = $this->createMock('Doctrine\ORM\EntityManagerInterface');
        $em->expects($this->exactly(3))
            ->method('getRepository')
            ->willReturnMap([
                ['LoginCidadaoOAuthBundle:Client', $repo],
                ['LoginCidadaoStatsBundle:Statistics', $statsRepo],
            ]);

        $args = new LifecycleEventArgs($authorization, $em);

        $subscriber = new StatisticsSubscriber();
        $subscriber->postRemove($args);
    }
}
