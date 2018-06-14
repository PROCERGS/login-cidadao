<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Tests\Handler;

use Doctrine\ORM\EntityManagerInterface;
use LoginCidadao\CoreBundle\Handler\AuthenticationSuccessHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\HttpUtils;

class AuthenticationSuccessHandlerTest extends TestCase
{

    public function testOnAuthenticationSuccess()
    {
        $ip = '::1';
        $username = 'username';

        $repo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')->disableOriginalConstructor()->getMock();
        $repo->expects($this->once())
            ->method('findOneBy')->with(['ip' => $ip, 'username' => $username])
            ->willReturn(null);

        /** @var EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject $em */
        $em = $this->createMock('Doctrine\ORM\EntityManagerInterface');
        $em->expects($this->once())->method('persist')
            ->with($this->isInstanceOf('LoginCidadao\CoreBundle\Entity\AccessSession'));
        $em->expects($this->once())->method('flush');
        $em->expects($this->once())->method('getRepository')->with('LoginCidadaoCoreBundle:AccessSession')
            ->willReturn($repo);

        /** @var HttpUtils $httpUtils */
        $httpUtils = $this->createMock('Symfony\Component\Security\Http\HttpUtils');

        /** @var Request|\PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()->getMock();
        $request->expects($this->once())->method('getClientIp')->willReturn($ip);
        $request->expects($this->atLeastOnce())->method('get')
            ->willReturn(['username' => $username]);

        /** @var TokenInterface $token */
        $token = $this->createMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');

        $handler = new AuthenticationSuccessHandler($httpUtils, $em, []);
        $handler->onAuthenticationSuccess($request, $token);
    }
}
