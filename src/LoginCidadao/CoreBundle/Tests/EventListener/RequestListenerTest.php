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

use LoginCidadao\CoreBundle\EventListener\RequestListener;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class RequestListenerTest extends TestCase
{
    public function testOnKernelRequest()
    {
        /** @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject $logger */
        $logger = $this->createMock('Psr\Log\LoggerInterface');
        $logger->expects($this->once())
            ->method('info')->with($this->stringContains('https://example.com'));

        $request = new Request([], [], [], [], [], ['HTTP_REFERER' => 'https://example.com']);

        /** @var GetResponseEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()->getMock();
        $event->expects($this->once())
            ->method('getRequestType')->willReturn(HttpKernelInterface::MASTER_REQUEST);
        $event->expects($this->once())
            ->method('getRequest')->willReturn($request);

        $listener = new RequestListener($logger);
        $listener->onKernelRequest($event);
    }

    public function testNoReferer()
    {
        /** @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject $logger */
        $logger = $this->createMock('Psr\Log\LoggerInterface');
        $logger->expects($this->never())->method('info');

        $request = new Request();

        /** @var GetResponseEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()->getMock();
        $event->expects($this->once())
            ->method('getRequestType')->willReturn(HttpKernelInterface::MASTER_REQUEST);
        $event->expects($this->once())
            ->method('getRequest')->willReturn($request);

        $listener = new RequestListener($logger);
        $listener->onKernelRequest($event);
    }

    public function testNotMasterRequest()
    {
        /** @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject $logger */
        $logger = $this->createMock('Psr\Log\LoggerInterface');
        $logger->expects($this->never())->method('info');

        /** @var GetResponseEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()->getMock();
        $event->expects($this->once())
            ->method('getRequestType')->willReturn(HttpKernelInterface::SUB_REQUEST);

        $listener = new RequestListener($logger);
        $listener->onKernelRequest($event);
    }
}
