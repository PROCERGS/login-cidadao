<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\BadgesControlBundle\Tests\Model;

use LoginCidadao\BadgesControlBundle\BadgesEvents;
use LoginCidadao\BadgesControlBundle\Event\ListBadgesEvent;
use LoginCidadao\BadgesControlBundle\Event\ListBearersEvent;
use LoginCidadao\BadgesControlBundle\Exception\BadgesNameCollisionException;
use LoginCidadao\BadgesControlBundle\Model\AbstractBadgesEventSubscriber;
use PHPUnit\Framework\TestCase;

class AbstractBadgesEventSubscriberTest extends TestCase
{
    public function testAbstractSubscriber()
    {
        $this->assertSame([
            BadgesEvents::BADGES_EVALUATE => ['onBadgeEvaluate', 0],
            BadgesEvents::BADGES_LIST_AVAILABLE => ['onBadgeListAvailable', 0],
            BadgesEvents::BADGES_LIST_BEARERS => ['onListBearersPreFilter', 0],
        ], AbstractBadgesEventSubscriber::getSubscribedEvents());

        /** @var AbstractBadgesEventSubscriber|\PHPUnit_Framework_MockObject_MockObject $stub */
        $stub = $this->getMockForAbstractClass('LoginCidadao\BadgesControlBundle\Model\AbstractBadgesEventSubscriber');
        $stub->expects($this->once())->method('onListBearers')
            ->with($this->isInstanceOf('LoginCidadao\BadgesControlBundle\Event\ListBearersEvent'));

        $stub->setName('namespace');

        /** @var ListBadgesEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMockBuilder('LoginCidadao\BadgesControlBundle\Event\ListBadgesEvent')
            ->disableOriginalConstructor()->getMock();
        $event->expects($this->once())->method('registerBadges')->with($stub);

        try {
            $stub->onBadgeListAvailable($event);
        } catch (BadgesNameCollisionException $e) {
            $this->fail("Unexpected exception");
        }

        $filterBadge = $this->createMock('LoginCidadao\BadgesControlBundle\Model\BadgeInterface');
        $filterBadge->expects($this->once())->method('getNamespace')->willReturn('namespace');

        /** @var ListBearersEvent|\PHPUnit_Framework_MockObject_MockObject $bearersEvent */
        $bearersEvent = $this->getMockBuilder('LoginCidadao\BadgesControlBundle\Event\ListBearersEvent')
            ->disableOriginalConstructor()->getMock();
        $bearersEvent->expects($this->once())->method('getBadge')->willReturn($filterBadge);

        $stub->onListBearersPreFilter($bearersEvent);

        $this->assertEmpty($stub->getAvailableBadges());
    }
}
