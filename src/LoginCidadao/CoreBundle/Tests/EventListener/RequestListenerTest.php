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

use FOS\UserBundle\Model\FosUserInterface;
use LoginCidadao\CoreBundle\EventListener\RequestListener;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class RequestListenerTest extends TestCase
{
    public function testOnKernelRequest()
    {
        $logger = $this->getLogger('https://example.com');

        $request = new Request([], [], [], [], [], ['HTTP_REFERER' => 'https://example.com']);

        $event = $this->getEvent(true, $request);

        $listener = new RequestListener($logger, $this->getTokenStorage(), $this->getRouter());
        $listener->onKernelRequest($event);
    }

    public function testNoReferer()
    {
        $event = $this->getEvent(true, new Request());

        $listener = new RequestListener($this->getLogger(), $this->getTokenStorage(), $this->getRouter());
        $listener->onKernelRequest($event);
    }

    public function testNotMasterRequest()
    {
        $event = $this->getEvent(false);

        $listener = new RequestListener($this->getLogger(), $this->getTokenStorage(), $this->getRouter());
        $listener->onKernelRequest($event);
    }

    public function testDisabledUser()
    {
        $user = $this->createMock(FosUserInterface::class);
        $user->expects($this->once())->method('isEnabled')->willReturn(false);

        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->once())->method('getUser')->willReturn($user);

        $event = $this->getEvent(true);
        $event->expects($this->once())->method('stopPropagation');
        $event->expects($this->once())->method('setResponse')
            ->with($this->isInstanceOf(RedirectResponse::class));

        $tokenStorage = $this->getTokenStorage();
        $tokenStorage->expects($this->once())->method('getToken')->willReturn($token);

        $router = $this->getRouter('fos_user_security_logout');

        $listener = new RequestListener($this->getLogger(), $tokenStorage, $router);
        $listener->onKernelRequest($event);
    }

    public function testLoggedInButNoUser()
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->once())->method('getUser')->willReturn(null);

        $event = $this->getEvent(true);
        $event->expects($this->never())->method('stopPropagation');
        $event->expects($this->never())->method('setResponse');

        $tokenStorage = $this->getTokenStorage();
        $tokenStorage->expects($this->once())->method('getToken')->willReturn($token);

        $router = $this->getRouter();
        $router->expects($this->never())->method('generate');

        $listener = new RequestListener($this->getLogger(), $tokenStorage, $router);
        $listener->onKernelRequest($event);
    }

    /**
     * @return MockObject|TokenStorageInterface
     */
    private function getTokenStorage()
    {
        return $this->createMock(TokenStorageInterface::class);
    }

    /**
     * @param string|null $expected
     * @return MockObject|RouterInterface
     */
    private function getRouter(string $expected = null)
    {
        $router = $this->createMock(RouterInterface::class);
        if (null !== $expected) {
            $router->expects($this->once())->method('generate')->with($expected)->willReturn($expected);
        }

        return $router;
    }

    /**
     * @param bool $isMasterRequest
     * @param Request|null $request
     * @return MockObject|GetResponseEvent
     */
    private function getEvent(bool $isMasterRequest, Request $request = null)
    {
        /** @var GetResponseEvent|MockObject $event */
        $event = $this->createMock(GetResponseEvent::class);
        $event->expects($this->once())->method('isMasterRequest')->willReturn($isMasterRequest);

        if (null === $request) {
            $request = new Request();
            $event->expects($this->any())->method('getRequest')->willReturn($request);
        } else {
            $event->expects($this->once())->method('getRequest')->willReturn($request);
        }

        return $event;
    }

    /**
     * @param string|null $expectedString
     * @return MockObject|LoggerInterface
     */
    private function getLogger(string $expectedString = null)
    {
        $logger = $this->createMock(LoggerInterface::class);
        if (null !== $expectedString) {
            $logger->expects($this->once())->method('info')->with($this->stringContains($expectedString));
        } else {
            $logger->expects($this->never())->method('info');
        }

        return $logger;
    }
}
