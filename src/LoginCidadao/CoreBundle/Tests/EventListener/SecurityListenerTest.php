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

use LoginCidadao\APIBundle\Security\Audit\ActionLogger;
use LoginCidadao\CoreBundle\Entity\Person;
use LoginCidadao\CoreBundle\EventListener\SecurityListener;
use LoginCidadao\CoreBundle\Helper\SecurityHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\SwitchUserRole;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;

class SecurityListenerTest extends TestCase
{
    public function testOnImpersonate()
    {
        $admin = new Person();
        $targetUser = new Person();
        $request = new Request();

        $securityHelper = $this->getSecurityHelper();
        $securityHelper->expects($this->once())->method('getUser')->willReturn($admin);
        $securityHelper->expects($this->once())
            ->method('isGranted')->with('ROLE_PREVIOUS_ADMIN')
            ->willReturn(false);

        $actionLogger = $this->getActionLogger();
        $actionLogger->expects($this->once())
            ->method('registerImpersonate')
            ->with($request, $targetUser, $admin, $this->isType('array'), true);

        $event = new SwitchUserEvent($request, $targetUser);

        $listener = new SecurityListener($securityHelper, $actionLogger);
        $listener->onSwitchUser($event);
    }

    public function testOnDeimpersonate()
    {
        $admin = new Person();
        $targetUser = new Person();
        $request = new Request();

        /** @var TokenInterface|\PHPUnit_Framework_MockObject_MockObject $sourceToken */
        $sourceToken = $this->createMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $sourceToken->expects($this->once())->method('getUser')->willReturn($admin);

        $securityHelper = $this->getSecurityHelper();
        $securityHelper->expects($this->once())
            ->method('getTokenRoles')
            ->willReturn([new SwitchUserRole('ROLE', $sourceToken)]);
        $securityHelper->expects($this->once())
            ->method('isGranted')->with('ROLE_PREVIOUS_ADMIN')
            ->willReturn(true);

        $actionLogger = $this->getActionLogger();
        $actionLogger->expects($this->once())
            ->method('registerImpersonate')
            ->with($request, $targetUser, $admin, $this->isType('array'), false);

        $event = new SwitchUserEvent($request, $targetUser);

        $listener = new SecurityListener($securityHelper, $actionLogger);
        $listener->onSwitchUser($event);
    }

    public function testOnSecurityInteractiveLogin()
    {
        $request = new Request();
        $person = new Person();

        /** @var TokenInterface|\PHPUnit_Framework_MockObject_MockObject $token */
        $token = $this->createMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token->expects($this->once())->method('getUser')->willReturn($person);

        $actionLogger = $this->getActionLogger();
        $actionLogger->expects($this->once())
            ->method('registerLogin')->with($request, $person, $this->isType('array'));

        $event = new InteractiveLoginEvent($request, $token);

        $listener = new SecurityListener($this->getSecurityHelper(), $actionLogger);
        $listener->onSecurityInteractiveLogin($event);
    }

    public function testOnKernelRequest()
    {
        $admin = new Person();

        $securityHelper = $this->getSecurityHelper();
        $securityHelper->expects($this->exactly(2))->method('getUser')->willReturn($admin);
        $securityHelper->expects($this->once())->method('checkPendingImpersonateReport')->with($admin);
        $securityHelper->expects($this->once())->method('isGranted')->with('FEATURE_IMPERSONATION_REPORTS')
            ->willReturn(true);

        /** @var GetResponseEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()->getMock();
        $event->expects($this->once())->method('getRequestType')
            ->willReturn(HttpKernelInterface::MASTER_REQUEST);

        $listener = new SecurityListener($securityHelper, $this->getActionLogger());
        $listener->onKernelRequest($event);
    }

    public function testOnKernelRequestNotMaster()
    {
        $securityHelper = $this->getSecurityHelper();
        $securityHelper->expects($this->never())->method('getUser');
        $securityHelper->expects($this->never())->method('checkPendingImpersonateReport');
        $securityHelper->expects($this->never())->method('isGranted');

        /** @var GetResponseEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()->getMock();
        $event->expects($this->once())->method('getRequestType')
            ->willReturn(HttpKernelInterface::SUB_REQUEST);

        $listener = new SecurityListener($securityHelper, $this->getActionLogger());
        $listener->onKernelRequest($event);
    }

    public function testOnKernelRequestNotPersonInterface()
    {
        $securityHelper = $this->getSecurityHelper();
        $securityHelper->expects($this->once())->method('getUser')->willReturn(new User('user', 'pass'));
        $securityHelper->expects($this->never())->method('checkPendingImpersonateReport');
        $securityHelper->expects($this->never())->method('isGranted');

        /** @var GetResponseEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()->getMock();
        $event->expects($this->once())->method('getRequestType')
            ->willReturn(HttpKernelInterface::MASTER_REQUEST);

        $listener = new SecurityListener($securityHelper, $this->getActionLogger());
        $listener->onKernelRequest($event);
    }

    public function testOnKernelRequestNotGranted()
    {
        $securityHelper = $this->getSecurityHelper();
        $securityHelper->expects($this->once())->method('getUser')->willReturn(new Person());
        $securityHelper->expects($this->never())->method('checkPendingImpersonateReport');
        $securityHelper->expects($this->once())->method('isGranted')->with('FEATURE_IMPERSONATION_REPORTS')
            ->willReturn(false);

        /** @var GetResponseEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()->getMock();
        $event->expects($this->once())->method('getRequestType')
            ->willReturn(HttpKernelInterface::MASTER_REQUEST);

        $listener = new SecurityListener($securityHelper, $this->getActionLogger());
        $listener->onKernelRequest($event);
    }

    /**
     * @return SecurityHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getSecurityHelper()
    {
        return $this->getMockBuilder('LoginCidadao\CoreBundle\Helper\SecurityHelper')
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * @return ActionLogger|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getActionLogger()
    {
        return $this->getMockBuilder('LoginCidadao\APIBundle\Security\Audit\ActionLogger')
            ->disableOriginalConstructor()->getMock();
    }
}
