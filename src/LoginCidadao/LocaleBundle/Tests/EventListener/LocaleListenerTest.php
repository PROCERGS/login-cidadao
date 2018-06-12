<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\LocaleBundle\Tests\EventListener;

use LoginCidadao\LocaleBundle\EventListener\LocaleListener;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleListenerTest extends TestCase
{

    public function testGetSubscribedEvents()
    {
        $this->assertSame([
            KernelEvents::REQUEST => [['onKernelRequest', 17]],
        ], LocaleListener::getSubscribedEvents());
    }

    public function testOnKernelRequestNoSession()
    {
        /** @var MockObject|GetResponseEvent $event */
        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()->getMock();
        $event->expects($this->once())
            ->method('getRequest')
            ->willReturn(new Request());

        $listener = new LocaleListener('en');
        $listener->onKernelRequest($event);
    }

    public function testLocaleFromAttributes()
    {
        $locale = 'pt_BR';
        $sessionName = 'sessionName';

        /** @var SessionInterface|\PHPUnit_Framework_MockObject_MockObject $session */
        $session = $this->createMock('Symfony\Component\HttpFoundation\Session\SessionInterface');
        $session->expects($this->once())->method('getName')->willReturn($sessionName);
        $session->expects($this->once())->method('set')->with('_locale', $locale);

        $request = new Request([], [], ['_locale' => $locale], [$sessionName => 'something']);
        $request->setSession($session);

        /** @var GetResponseEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()->getMock();
        $event->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        $listener = new LocaleListener('en');
        $listener->onKernelRequest($event);
    }

    public function testLocaleFromSession()
    {
        $locale = 'pt_BR';
        $sessionName = 'sessionName';

        /** @var SessionInterface|\PHPUnit_Framework_MockObject_MockObject $session */
        $session = $this->createMock('Symfony\Component\HttpFoundation\Session\SessionInterface');
        $session->expects($this->once())->method('getName')->willReturn($sessionName);
        $session->expects($this->once())->method('get')->with('_locale', 'en')->willReturn($locale);

        $request = new Request([], [], [], [$sessionName => 'something']);
        $request->setSession($session);

        /** @var GetResponseEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()->getMock();
        $event->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        $listener = new LocaleListener('en');
        $listener->onKernelRequest($event);

        $this->assertSame($locale, $request->getLocale());
    }
}
