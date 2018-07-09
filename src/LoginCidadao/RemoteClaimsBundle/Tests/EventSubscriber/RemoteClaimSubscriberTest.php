<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\RemoteClaimsBundle\Tests\EventSubscriber;

use LoginCidadao\RemoteClaimsBundle\Event\UpdateRemoteClaimUriEvent;
use LoginCidadao\RemoteClaimsBundle\EventSubscriber\RemoteClaimSubscriber;
use LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimManagerInterface;
use LoginCidadao\RemoteClaimsBundle\Model\TagUri;
use LoginCidadao\RemoteClaimsBundle\RemoteClaimEvents;
use PHPUnit\Framework\TestCase;

class RemoteClaimSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents()
    {
        $this->assertEquals([
            RemoteClaimEvents::REMOTE_CLAIM_UPDATE_URI => 'onRemoteClaimUriUpdate',
        ], RemoteClaimSubscriber::getSubscribedEvents());
    }

    public function testOnRemoteClaimUriUpdate()
    {
        $claimName = new TagUri();
        $uri = 'https://dummy.tld';
        $event = new UpdateRemoteClaimUriEvent($claimName, $uri);

        $manager = $this->getRemoteClaimManager();
        $manager->expects($this->once())->method('updateRemoteClaimUri')
            ->with($claimName, $uri);

        $subscriber = new RemoteClaimSubscriber($manager);
        $subscriber->onRemoteClaimUriUpdate($event);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|RemoteClaimManagerInterface
     */
    private function getRemoteClaimManager()
    {
        return $this->createMock('LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimManagerInterface');
    }
}
