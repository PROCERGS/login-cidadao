<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Tests\EventListener;

use LoginCidadao\CoreBundle\Event\GetClientEvent;
use LoginCidadao\CoreBundle\Event\LoginCidadaoCoreEvents;
use LoginCidadao\OAuthBundle\Entity\Client;
use LoginCidadao\OpenIDBundle\Entity\ClientMetadata;
use LoginCidadao\OpenIDBundle\Entity\ClientMetadataRepository;
use LoginCidadao\OpenIDBundle\EventListener\CoreEventsSubscriber;
use LoginCidadao\OpenIDBundle\Validator\SectorIdentifierUriChecker;
use PHPUnit\Framework\TestCase;

class CoreEventsSubscriberTest extends TestCase
{

    public function testGetSubscribedEvents()
    {
        $this->assertSame([
            LoginCidadaoCoreEvents::GET_CLIENT => ['onGetClient', 10],
        ], CoreEventsSubscriber::getSubscribedEvents());
    }

    public function testOnGetClient()
    {
        $client = new Client();
        $event = new GetClientEvent($client);
        $metadata = (new ClientMetadata())
            ->setClient($client);

        $repo = $this->getClientMetadataRepository();
        $repo->expects($this->once())
            ->method('findOneBy')->with(['client' => $client])
            ->willReturn($metadata);

        $uriChecker = $this->getSectorIdentifierUriChecker();
        $uriChecker->expects($this->once())
            ->method('recheck')->with($metadata);

        $subscriber = new CoreEventsSubscriber($repo, $uriChecker, true);
        $subscriber->onGetClient($event);
    }

    public function testOnGetClientNoRevalidation()
    {
        $repo = $this->getClientMetadataRepository();
        $repo->expects($this->never())->method('findOneBy');

        $uriChecker = $this->getSectorIdentifierUriChecker();

        $subscriber = new CoreEventsSubscriber($repo, $uriChecker, false);

        $subscriber->onGetClient(new GetClientEvent());
    }

    /**
     * @return ClientMetadataRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getClientMetadataRepository()
    {
        return $this->getMockBuilder('LoginCidadao\OpenIDBundle\Entity\ClientMetadataRepository')
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * @return SectorIdentifierUriChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getSectorIdentifierUriChecker()
    {
        return $this->getMockBuilder('LoginCidadao\OpenIDBundle\Validator\SectorIdentifierUriChecker')
            ->disableOriginalConstructor()->getMock();
    }
}
