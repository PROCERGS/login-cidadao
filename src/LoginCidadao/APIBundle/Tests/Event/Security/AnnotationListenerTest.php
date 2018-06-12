<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\APIBundle\Tests\Event\Security;

use Doctrine\Common\Annotations\Reader;
use LoginCidadao\APIBundle\Controller\PersonController;
use LoginCidadao\APIBundle\Event\Security\AnnotationListener;
use LoginCidadao\APIBundle\Security\Audit\ActionLogger;
use LoginCidadao\APIBundle\Security\Audit\Annotation\Loggable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class AnnotationListenerTest extends TestCase
{

    public function testOnKernelResponse()
    {
        $logId = 'logId666';
        $responseCode = 200;

        /** @var Reader|\PHPUnit_Framework_MockObject_MockObject $reader */
        $reader = $this->createMock(Reader::class);

        /** @var ActionLogger|\PHPUnit_Framework_MockObject_MockObject $logger */
        $logger = $this->getMockBuilder(ActionLogger::class)->disableOriginalConstructor()->getMock();
        $logger->expects($this->once())->method('updateResponseCode')->with($logId, $responseCode);

        $attrs = [
            '_loggable' => [
                (new Loggable(['type' => 'LOGIN']))
                    ->setActionLogId($logId),
            ],
        ];
        $request = new Request([], [], $attrs);

        $response = new Response('', $responseCode);

        /** @var FilterResponseEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMockBuilder(FilterResponseEvent::class)->disableOriginalConstructor()->getMock();
        $event->expects($this->once())->method('getRequest')->willReturn($request);
        $event->expects($this->once())->method('getResponse')->willReturn($response);

        $listener = new AnnotationListener($reader, $logger);
        $listener->onKernelResponse($event);
    }

    public function testOnKernelController()
    {
        $logId = 'logId666';
        $controller = new PersonController();
        $request = new Request();

        $annotation = (new Loggable(['type' => 'LOGIN']))
            ->setActionLogId($logId);

        /** @var Reader|\PHPUnit_Framework_MockObject_MockObject $reader */
        $reader = $this->createMock(Reader::class);
        $reader->expects($this->once())
            ->method('getMethodAnnotations')->willReturn([$annotation]);

        /** @var ActionLogger|\PHPUnit_Framework_MockObject_MockObject $logger */
        $logger = $this->getMockBuilder(ActionLogger::class)->disableOriginalConstructor()->getMock();
        $logger->expects($this->once())
            ->method('logActivity')
            ->with($request, $annotation, [$controller, 'getPersonAction']);

        /** @var FilterControllerEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMockBuilder(FilterControllerEvent::class)->disableOriginalConstructor()->getMock();
        $event->expects($this->once())
            ->method('getController')
            ->willReturn([$controller, 'getPersonAction']);
        $event->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        $listener = new AnnotationListener($reader, $logger);
        $listener->onKernelController($event);
    }

    public function testOnKernelControllerMissingController()
    {
        /** @var Reader|\PHPUnit_Framework_MockObject_MockObject $reader */
        $reader = $this->createMock(Reader::class);

        /** @var ActionLogger|\PHPUnit_Framework_MockObject_MockObject $logger */
        $logger = $this->getMockBuilder(ActionLogger::class)->disableOriginalConstructor()->getMock();

        /** @var FilterControllerEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMockBuilder(FilterControllerEvent::class)->disableOriginalConstructor()->getMock();
        $event->expects($this->once())
            ->method('getController')
            ->willReturn(null);

        $listener = new AnnotationListener($reader, $logger);
        $listener->onKernelController($event);
    }
}
