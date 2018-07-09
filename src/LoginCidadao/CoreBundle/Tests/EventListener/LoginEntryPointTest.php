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

use LoginCidadao\CoreBundle\Event\LoginCidadaoCoreEvents;
use LoginCidadao\CoreBundle\EventListener\LoginEntryPoint;
use LoginCidadao\CoreBundle\Service\RegisterRequestedScope;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\HttpUtils;

class LoginEntryPointTest extends TestCase
{

    public function testStart()
    {
        $request = new Request();
        $redirect = new RedirectResponse('https://example.com');

        /** @var HttpUtils|\PHPUnit_Framework_MockObject_MockObject $httpUtils */
        $httpUtils = $this->createMock('Symfony\Component\Security\Http\HttpUtils');
        $httpUtils->expects($this->once())
            ->method('createRedirectResponse')->with($request, 'fos_user_security_login')
            ->willReturn($redirect);

        /** @var EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject $dispatcher */
        $dispatcher = $this->createMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $dispatcher->expects($this->once())
            ->method('dispatch')->with(
                LoginCidadaoCoreEvents::AUTHENTICATION_ENTRY_POINT_START,
                $this->isInstanceOf('LoginCidadao\TaskStackBundle\Event\EntryPointStartEvent')
            );

        /** @var RegisterRequestedScope|\PHPUnit_Framework_MockObject_MockObject $registerScopeService */
        $registerScopeService = $this->createMock('LoginCidadao\CoreBundle\Service\RegisterRequestedScope');
        $registerScopeService->expects($this->once())
            ->method('registerRequestedScope')->with($request);

        $entryPoint = new LoginEntryPoint($httpUtils, $dispatcher, $registerScopeService);
        $this->assertSame($redirect, $entryPoint->start($request));
    }
}
