<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Tests\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use LoginCidadao\CoreBundle\EventListener\CheckDeployEventSubscriber;
use LoginCidadao\OAuthBundle\Entity\Client;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelEvents;

class CheckDeployEventSubscriberTest extends TestCase
{

    public function testGetSubscribedEvents()
    {
        $this->assertSame([KernelEvents::REQUEST => 'checkDeploy'], CheckDeployEventSubscriber::getSubscribedEvents());
    }

    public function testCheckDeployOk()
    {
        $defaultUid = 'default-client';

        $cityRepo = $this->getMockBuilder('LoginCidadao\CoreBundle\Entity\CityRepository')
            ->disableOriginalConstructor()->getMock();
        $cityRepo->expects($this->once())
            ->method('countCities')->willReturn(5000);

        $clientRepo = $this->getMockBuilder('LoginCidadao\OAuthBundle\Entity\ClientRepository')
            ->disableOriginalConstructor()->getMock();
        $clientRepo->expects($this->once())
            ->method('countClients')->willReturn(10);
        $clientRepo->expects($this->once())
            ->method('findOneBy')->with(['uid' => $defaultUid])
            ->willReturn(new Client());

        $cache = $this->createMock('Doctrine\Common\Cache\CacheProvider');

        /** @var EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject $em */
        $em = $this->createMock('Doctrine\ORM\EntityManagerInterface');
        $em->expects($this->exactly(2))
            ->method('getRepository')
            ->willReturnMap([
                ['LoginCidadaoCoreBundle:City', $cityRepo],
                ['LoginCidadaoOAuthBundle:Client', $clientRepo],
            ]);

        $subscriber = new CheckDeployEventSubscriber($em, $defaultUid);
        $subscriber->checkDeploy();

        $subscriber->setCacheProvider($cache);
        $subscriber->checkDeploy();
    }

    public function testCheckDeployNotOk()
    {
        $this->expectException('\RuntimeException');
        $this->expectExceptionMessage('Make sure you did run the populate database command.');
        $defaultUid = 'default-client';

        $cityRepo = $this->getMockBuilder('LoginCidadao\CoreBundle\Entity\CityRepository')
            ->disableOriginalConstructor()->getMock();
        $clientRepo = $this->getMockBuilder('LoginCidadao\OAuthBundle\Entity\ClientRepository')
            ->disableOriginalConstructor()->getMock();

        $cache = $this->createMock('Doctrine\Common\Cache\CacheProvider');

        /** @var EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject $em */
        $em = $this->createMock('Doctrine\ORM\EntityManagerInterface');
        $em->expects($this->exactly(2))
            ->method('getRepository')
            ->willReturnMap([
                ['LoginCidadaoCoreBundle:City', $cityRepo],
                ['LoginCidadaoOAuthBundle:Client', $clientRepo],
            ]);

        $subscriber = new CheckDeployEventSubscriber($em, $defaultUid);
        $subscriber->setCacheProvider($cache);
        $subscriber->checkDeploy();
    }
}
