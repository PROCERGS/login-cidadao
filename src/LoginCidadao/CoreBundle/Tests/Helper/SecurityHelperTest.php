<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Tests\Helper;

use LoginCidadao\APIBundle\Entity\ActionLogRepository;
use LoginCidadao\CoreBundle\Entity\Person;
use LoginCidadao\CoreBundle\Helper\ExtremeNotificationsHelper;
use LoginCidadao\CoreBundle\Helper\SecurityHelper;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\User;

class SecurityHelperTest extends TestCase
{
    public function testGetLoggedInUserLevelNonDefault()
    {
        $authChecker = $this->getAuthChecker();
        $authChecker->expects($this->atLeastOnce())
            ->method('isGranted')
            ->willReturnMap([
                ['ROLE_SUPER_ADMIN', null, false],
                ['ROLE_ADMIN', null, false],
                ['ROLE_SUPER_USER', null, true],
                ['ROLE_DEV', null, false],
                ['ROLE_USER', null, false],
            ]);

        $helper = new SecurityHelper(
            $authChecker,
            $this->getTokenStorage(),
            $this->getActionLogRepository(),
            $this->getExtremeNotificationsHelper(),
            $this->getRouter(),
            'cookieName'
        );

        $this->assertSame(2, $helper->getLoggedInUserLevel());
    }

    public function testGetLoggedInUserLevelDefault()
    {
        $authChecker = $this->getAuthChecker();
        $authChecker->expects($this->atLeastOnce())
            ->method('isGranted');

        $helper = new SecurityHelper(
            $authChecker,
            $this->getTokenStorage(),
            $this->getActionLogRepository(),
            $this->getExtremeNotificationsHelper(),
            $this->getRouter(),
            'cookieName'
        );

        $this->assertSame(0, $helper->getLoggedInUserLevel());
    }

    public function testCheckNoPendingImpersonateReport()
    {
        $person = new Person();

        $repo = $this->getActionLogRepository();
        $repo->expects($this->once())
            ->method('countImpersonatonsWithoutReports')->with($person)
            ->willReturn(0);

        $helper = new SecurityHelper(
            $this->getAuthChecker(),
            $this->getTokenStorage(),
            $repo,
            $this->getExtremeNotificationsHelper(),
            $this->getRouter(),
            'cookieName'
        );

        $helper->checkPendingImpersonateReport($person);
    }

    public function testCheckPendingImpersonateReport()
    {
        $count = 2;
        $url = 'https://example.com';
        $person = new Person();

        $repo = $this->getActionLogRepository();
        $repo->expects($this->once())
            ->method('countImpersonatonsWithoutReports')
            ->willReturn($count);

        $router = $this->getRouter();
        $router->expects($this->once())
            ->method('generate')->with('lc_admin_impersonation_report_index')
            ->willReturn($url);

        $parameters = ['%url%' => $url, '%count%' => $count];

        $extremeNotifHelper = $this->getExtremeNotificationsHelper();
        $extremeNotifHelper->expects($this->once())
            ->method('addTransChoice')->with('admin.impersonation_report.pending.notification', $count, $parameters);

        $helper = new SecurityHelper(
            $this->getAuthChecker(),
            $this->getTokenStorage(),
            $repo,
            $extremeNotifHelper,
            $router,
            'cookieName'
        );

        $helper->checkPendingImpersonateReport($person);
    }

    public function testGetRoleLevel()
    {
        $helper = new SecurityHelper(
            $this->getAuthChecker(),
            $this->getTokenStorage(),
            $this->getActionLogRepository(),
            $this->getExtremeNotificationsHelper(),
            $this->getRouter(),
            'cookieName'
        );

        $roles = [
            'ROLE_SUPER_ADMIN' => 4,
            'ROLE_ADMIN' => 3,
            'ROLE_SUPER_USER' => 2,
            'ROLE_DEV' => 1,
            'ROLE_USER' => 0,
        ];

        foreach ($roles as $role => $expected) {
            $this->assertSame($expected, $helper->getRoleLevel($role));
        }

        $this->assertSame(4, $helper->getRoleLevel('OTHER_ROLE'));
    }

    public function testGetUser()
    {
        $person = new Person();

        $token = $this->createMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token->expects($this->once())->method('getUser')->willReturn($person);

        $tokenStorage = $this->getTokenStorage();
        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $helper = new SecurityHelper(
            $this->getAuthChecker(),
            $tokenStorage,
            $this->getActionLogRepository(),
            $this->getExtremeNotificationsHelper(),
            $this->getRouter(),
            'cookieName'
        );

        $this->assertSame($person, $helper->getUser());
    }

    public function testGetUserNotPersonInterface()
    {
        $user = new User('username', 'password');

        $token = $this->createMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token->expects($this->once())->method('getUser')->willReturn($user);

        $tokenStorage = $this->getTokenStorage();
        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $helper = new SecurityHelper(
            $this->getAuthChecker(),
            $tokenStorage,
            $this->getActionLogRepository(),
            $this->getExtremeNotificationsHelper(),
            $this->getRouter(),
            'cookieName'
        );

        $this->assertNull($helper->getUser());
    }

    public function testGetUserException()
    {
        $token = $this->createMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token->expects($this->once())->method('getUser')->willThrowException(new \RuntimeException());

        $tokenStorage = $this->getTokenStorage();
        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $helper = new SecurityHelper(
            $this->getAuthChecker(),
            $tokenStorage,
            $this->getActionLogRepository(),
            $this->getExtremeNotificationsHelper(),
            $this->getRouter(),
            'cookieName'
        );

        $this->assertNull($helper->getUser());
    }

    public function testGetUserNoToken()
    {
        $tokenStorage = $this->getTokenStorage();
        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(null);

        $helper = new SecurityHelper(
            $this->getAuthChecker(),
            $tokenStorage,
            $this->getActionLogRepository(),
            $this->getExtremeNotificationsHelper(),
            $this->getRouter(),
            'cookieName'
        );

        $this->assertNull($helper->getUser());
    }

    public function testGetTargetPersonLevel()
    {
        $personLvl3 = new Person();
        $personLvl3->setRoles([
            'ROLE_DEV',
            'ROLE_ADMIN',
        ]);

        /** @var PersonInterface|\PHPUnit_Framework_MockObject_MockObject $personLvlDefault */
        $personLvlDefault = $this->createMock('LoginCidadao\CoreBundle\Model\PersonInterface');
        $personLvlDefault->expects($this->once())
            ->method('getRoles')
            ->willReturn(['OTHER_ROLE']);

        $helper = new SecurityHelper(
            $this->getAuthChecker(),
            $this->getTokenStorage(),
            $this->getActionLogRepository(),
            $this->getExtremeNotificationsHelper(),
            $this->getRouter(),
            'cookieName'
        );

        $this->assertSame(3, $helper->getTargetPersonLevel($personLvl3));
        $this->assertSame(0, $helper->getTargetPersonLevel($personLvlDefault));
    }

    public function testLogout()
    {
        $rememberMe = 'cookieName';

        /** @var SessionInterface|\PHPUnit_Framework_MockObject_MockObject $session */
        $session = $this->createMock('Symfony\Component\HttpFoundation\Session\SessionInterface');
        $session->expects($this->once())->method('invalidate');

        $request = new Request();
        $request->setSession($session);

        /** @var ResponseHeaderBag|\PHPUnit_Framework_MockObject_MockObject $headers */
        $headers = $this->createMock('Symfony\Component\HttpFoundation\ResponseHeaderBag');
        $headers->expects($this->once())->method('clearCookie')->with($rememberMe);

        $response = new Response();
        $response->headers = $headers;

        $tokenStorage = $this->getTokenStorage();
        $tokenStorage->expects($this->once())->method('setToken')->with(null);

        $helper = new SecurityHelper(
            $this->getAuthChecker(),
            $tokenStorage,
            $this->getActionLogRepository(),
            $this->getExtremeNotificationsHelper(),
            $this->getRouter(),
            $rememberMe
        );

        $helper->logout($request, $response);
    }

    public function testIsGranted()
    {
        $attributes = ['THE_ROLE'];
        $object = new \stdClass();

        $authChecker = $this->getAuthChecker();
        $authChecker->expects($this->atLeastOnce())
            ->method('isGranted')->with($attributes, $object)
            ->willReturn(true);

        $helper = new SecurityHelper(
            $authChecker,
            $this->getTokenStorage(),
            $this->getActionLogRepository(),
            $this->getExtremeNotificationsHelper(),
            $this->getRouter(),
            'cookieName'
        );

        $this->assertTrue($helper->isGranted($attributes, $object));
    }

    public function testGetTokenRoles()
    {
        $token = $this->createMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token->expects($this->once())->method('getRoles')->willReturn([]);

        $tokenStorage = $this->getTokenStorage();
        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $helper = new SecurityHelper(
            $this->getAuthChecker(),
            $tokenStorage,
            $this->getActionLogRepository(),
            $this->getExtremeNotificationsHelper(),
            $this->getRouter(),
            'cookieName'
        );

        $this->assertEmpty($helper->getTokenRoles());
    }

    public function testHasToken()
    {
        $token = $this->createMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');

        $tokenStorage = $this->getTokenStorage();
        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $helper = new SecurityHelper(
            $this->getAuthChecker(),
            $tokenStorage,
            $this->getActionLogRepository(),
            $this->getExtremeNotificationsHelper(),
            $this->getRouter(),
            'cookieName'
        );

        $this->assertTrue($helper->hasToken());
    }

    public function testIsOAuthToken()
    {
        $token = $this->getMockBuilder('FOS\OAuthServerBundle\Security\Authentication\Token\OAuthToken')
            ->disableOriginalConstructor()->getMock();

        $tokenStorage = $this->getTokenStorage();
        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $helper = new SecurityHelper(
            $this->getAuthChecker(),
            $tokenStorage,
            $this->getActionLogRepository(),
            $this->getExtremeNotificationsHelper(),
            $this->getRouter(),
            'cookieName'
        );

        $this->assertTrue($helper->isOAuthToken());
    }

    /**
     * @return AuthorizationCheckerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getAuthChecker()
    {
        return $this->createMock('Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface');
    }

    /**
     * @return TokenStorageInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getTokenStorage()
    {
        return $this->createMock('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface');
    }

    /**
     * @return ActionLogRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getActionLogRepository()
    {
        return $this->getMockBuilder('LoginCidadao\APIBundle\Entity\ActionLogRepository')
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * @return ExtremeNotificationsHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getExtremeNotificationsHelper()
    {
        return $this->getMockBuilder('LoginCidadao\CoreBundle\Helper\ExtremeNotificationsHelper')
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * @return RouterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getRouter()
    {
        return $this->createMock('Symfony\Component\Routing\RouterInterface');
    }
}
