<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\BadgesControlBundle\Tests\Event;

use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;
use LoginCidadao\APIBundle\Service\VersionService;
use LoginCidadao\BadgesControlBundle\Event\SerializationSubscriber;
use LoginCidadao\BadgesControlBundle\Handler\BadgesHandler;
use LoginCidadao\BadgesControlBundle\Model\Badge;

class SerializationSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|BadgesHandler
     */
    private function getBadgesHandler()
    {
        return $this->getMockBuilder('LoginCidadao\BadgesControlBundle\Handler\BadgesHandler')
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|VersionService
     */
    private function getVersionService()
    {
        return $this->getMockBuilder('LoginCidadao\APIBundle\Service\VersionService')
            ->disableOriginalConstructor()->getMock();
    }

    public function testGetSubscribedEvents()
    {
        $this->assertEquals([
            [
                'event' => 'serializer.pre_serialize',
                'method' => 'onPreSerialize',
                'class' => 'LoginCidadao\CoreBundle\Model\PersonInterface',
            ],
            [
                'event' => 'serializer.post_serialize',
                'method' => 'onPostSerialize',
                'class' => 'LoginCidadao\CoreBundle\Model\PersonInterface',
            ],
        ], SerializationSubscriber::getSubscribedEvents());
    }

    public function testOnPreSerializePerson()
    {
        $person = $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');

        /** @var \PHPUnit_Framework_MockObject_MockObject|PreSerializeEvent $event */
        $event = $this->getMockBuilder('JMS\Serializer\EventDispatcher\PreSerializeEvent')
            ->disableOriginalConstructor()->getMock();
        $event->expects($this->once())->method('getObject')->willReturn($person);

        $handler = $this->getBadgesHandler();
        $handler->expects($this->once())->method('evaluate')->with($person);

        $subscriber = new SerializationSubscriber($handler, $this->getVersionService());
        $subscriber->onPreSerialize($event);
    }

    public function testOnPreSerializeNonPerson()
    {
        $object = new \stdClass();

        /** @var \PHPUnit_Framework_MockObject_MockObject|PreSerializeEvent $event */
        $event = $this->getMockBuilder('JMS\Serializer\EventDispatcher\PreSerializeEvent')
            ->disableOriginalConstructor()->getMock();
        $event->expects($this->once())->method('getObject')->willReturn($object);

        $handler = $this->getBadgesHandler();
        $handler->expects($this->never())->method('evaluate');

        $subscriber = new SerializationSubscriber($handler, $this->getVersionService());
        $subscriber->onPreSerialize($event);
    }

    public function testOnPostSerializePersonV1()
    {
        $badges = [
            new Badge('namespace', 'name', 'data'),
        ];

        $badgesSerialized = [
            'namespace.name' => 'data',
        ];

        $person = $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');
        $person->expects($this->once())->method('getBadges')->willReturn($badges);

        $visitor = $this->getMockBuilder('JMS\Serializer\GenericSerializationVisitor')
            ->disableOriginalConstructor()->getMock();
        $visitor->expects($this->once())->method('addData')->with('badges', $badgesSerialized);

        /** @var \PHPUnit_Framework_MockObject_MockObject|ObjectEvent $event */
        $event = $this->getMockBuilder('JMS\Serializer\EventDispatcher\ObjectEvent')
            ->disableOriginalConstructor()->getMock();
        $event->expects($this->once())->method('getObject')->willReturn($person);
        $event->expects($this->once())->method('getVisitor')->willReturn($visitor);

        $versionService = $this->getVersionService();
        $versionService->expects($this->once())->method('getVersionFromRequest')->willReturn([1, 0, 0]);
        $versionService->expects($this->once())->method('getString')->willReturn('1.0.0');

        $subscriber = new SerializationSubscriber($this->getBadgesHandler(), $versionService);
        $subscriber->onPostSerialize($event);
    }

    public function testOnPostSerializePersonV2()
    {
        $person = $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');

        /** @var \PHPUnit_Framework_MockObject_MockObject|ObjectEvent $event */
        $event = $this->getMockBuilder('JMS\Serializer\EventDispatcher\ObjectEvent')
            ->disableOriginalConstructor()->getMock();
        $event->expects($this->once())->method('getObject')->willReturn($person);
        $event->expects($this->never())->method('getVisitor');

        $versionService = $this->getVersionService();
        $versionService->expects($this->once())->method('getVersionFromRequest')->willReturn([2, 0, 0]);
        $versionService->expects($this->once())->method('getString')->willReturn('2.0.0');

        $subscriber = new SerializationSubscriber($this->getBadgesHandler(), $versionService);
        $subscriber->onPostSerialize($event);
    }

    public function testOnPostSerializeNonPerson()
    {
        $object = new \stdClass();

        /** @var \PHPUnit_Framework_MockObject_MockObject|ObjectEvent $event */
        $event = $this->getMockBuilder('JMS\Serializer\EventDispatcher\ObjectEvent')
            ->disableOriginalConstructor()->getMock();
        $event->expects($this->once())->method('getObject')->willReturn($object);
        $event->expects($this->never())->method('getVisitor');

        $subscriber = new SerializationSubscriber($this->getBadgesHandler(), $this->getVersionService());
        $subscriber->onPostSerialize($event);
    }
}
