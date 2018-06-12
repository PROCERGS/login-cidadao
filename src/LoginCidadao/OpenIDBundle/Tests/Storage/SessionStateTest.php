<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Tests\Storage;

use LoginCidadao\OAuthBundle\Entity\Client;
use LoginCidadao\OpenIDBundle\Entity\ClientMetadata;
use LoginCidadao\OpenIDBundle\Manager\ClientManager;
use LoginCidadao\OpenIDBundle\Storage\SessionState;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SessionStateTest extends TestCase
{
    public function testOnKernelResponseAddCookie()
    {
        /** @var ResponseHeaderBag|\PHPUnit_Framework_MockObject_MockObject $headers */
        $headers = $this->createMock('Symfony\Component\HttpFoundation\ResponseHeaderBag');
        $headers->expects($this->once())->method('setCookie')
            ->with($this->isInstanceOf('Symfony\Component\HttpFoundation\Cookie'));

        $response = new Response();
        $response->headers = $headers;

        $token = $this->createMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token->expects($this->once())->method('serialize')->willReturn('serialized');

        $tokenStorage = $this->getTokenStorage();
        $tokenStorage->expects($this->once())
            ->method('getToken')->willReturn($token);

        $event = $this->getFilterResponseEvent($response);

        $sessionState = new SessionState($this->getClientManager(), $tokenStorage);
        $sessionState->onKernelResponse($event);
    }

    public function testOnKernelResponseRemoveCookie()
    {
        /** @var ResponseHeaderBag|\PHPUnit_Framework_MockObject_MockObject $headers */
        $headers = $this->createMock('Symfony\Component\HttpFoundation\ResponseHeaderBag');
        $headers->expects($this->once())->method('removeCookie')->with('session_state');

        $response = new Response();
        $response->headers = $headers;

        $event = $this->getFilterResponseEvent($response);

        $sessionState = new SessionState($this->getClientManager(), $this->getTokenStorage());
        $sessionState->onKernelResponse($event);
    }

    public function testGetSessionState()
    {
        $clientId = 'client_id';
        $sessionId = 'session_id';
        $client = (new Client())
            ->setMetadata((new ClientMetadata())
                ->setClientUri($url = 'https://example.com'));

        $clientManager = $this->getClientManager();
        $clientManager->expects($this->once())
            ->method('getClientById')->with($clientId)->willReturn($client);

        $sessionState = new SessionState($clientManager, $this->getTokenStorage());

        $state = $sessionState->getSessionState($clientId, $sessionId);
        $generatedSalt = explode('.', $state)[1];

        $expectedState = hash('sha256', $clientId.$url.$sessionId.$generatedSalt).".{$generatedSalt}";
        $this->assertSame($expectedState, $state);
    }

    /**
     * @return TokenStorageInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getTokenStorage()
    {
        return $this->createMock('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface');
    }

    /**
     * @return ClientManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getClientManager()
    {
        return $this->getMockBuilder('LoginCidadao\OpenIDBundle\Manager\ClientManager')
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * @return FilterResponseEvent|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getFilterResponseEvent($response)
    {
        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\FilterResponseEvent')
            ->disableOriginalConstructor()->getMock();
        $event->expects($this->once())->method('isMasterRequest')->willReturn(true);
        $event->expects($this->once())->method('getResponse')->willReturn($response);

        return $event;
    }
}
