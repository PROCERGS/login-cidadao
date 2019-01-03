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

use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;
use LoginCidadao\CoreBundle\EventListener\PersonSerializeEventListener;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Templating\Helper\AssetsHelper;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Kernel;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class PersonSerializeEventListenerTest extends TestCase
{

    public function testGetSubscribedEvents()
    {
        $this->assertSame([
            [
                'event' => 'serializer.pre_serialize',
                'method' => 'onPreSerialize',
                'class' => 'LoginCidadao\CoreBundle\Model\PersonInterface',
            ],
        ], PersonSerializeEventListener::getSubscribedEvents());
    }

    public function testListener()
    {
        $request = new Request();
        $person = $this->createMock(PersonInterface::class);

        /** @var PreSerializeEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMockBuilder(PreSerializeEvent::class)
            ->disableOriginalConstructor()->getMock();
        $event->expects($this->once())
            ->method('getObject')->willReturn($person);

        /** @var UploaderHelper $uploadHelper */
        $uploadHelper = $this->getMockBuilder(UploaderHelper::class)
            ->disableOriginalConstructor()->getMock();

        /** @var Packages $templateHelper */
        $templateHelper = $this->getMockBuilder(Packages::class)
            ->disableOriginalConstructor()->getMock();

        /** @var Kernel $kernel */
        $kernel = $this->getMockBuilder(Kernel::class)
            ->disableOriginalConstructor()->getMock();

        /** @var RequestStack|\PHPUnit_Framework_MockObject_MockObject $requestStack */
        $requestStack = $this->getMockBuilder(RequestStack::class)
            ->disableOriginalConstructor()->getMock();
        $requestStack->expects($this->once())
            ->method('getCurrentRequest')->willReturn($request);

        $listener = new PersonSerializeEventListener($uploadHelper, $templateHelper, $kernel, $requestStack);
        $listener->onPreSerialize($event);
    }
}
