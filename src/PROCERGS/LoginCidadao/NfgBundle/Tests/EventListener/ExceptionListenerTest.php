<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\NfgBundle\Tests\EventListener;

use PROCERGS\LoginCidadao\NfgBundle\EventListener\ExceptionListener;
use PROCERGS\LoginCidadao\NfgBundle\Exception\ConnectionNotFoundException;
use PROCERGS\LoginCidadao\NfgBundle\Exception\CpfMismatchException;
use PROCERGS\LoginCidadao\NfgBundle\Exception\EmailInUseException;
use PROCERGS\LoginCidadao\NfgBundle\Exception\MissingRequiredInformationException;
use PROCERGS\LoginCidadao\NfgBundle\Exception\NfgAccountCollisionException;
use PROCERGS\LoginCidadao\NfgBundle\Exception\NfgServiceUnavailableException;
use PROCERGS\LoginCidadao\NfgBundle\Tests\TestsUtil;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ExceptionListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testEmailInUse()
    {
        $this->expectRedirect(new EmailInUseException(), 'nfg_help_email_in_use');
    }

    public function testServiceUnavailable()
    {
        $this->expectRedirect(new NfgServiceUnavailableException(), 'nfg_unavailable');
    }

    public function testCpfMismatch()
    {
        $this->expectRedirect(new CpfMismatchException(), 'nfg_help_cpf_did_not_match');
    }

    public function testNfgCollisionException()
    {
        $this->expectRedirect(new NfgAccountCollisionException(), 'nfg_help_already_connected');
    }

    public function testMissingRequiredInformationException()
    {
        $this->expectRedirect(new MissingRequiredInformationException(), 'nfg_missing_info');
    }

    public function testConnectionNotFoundExceptionWithoutFlash()
    {
        $this->expectRedirect(new ConnectionNotFoundException(), 'fos_user_security_login');
    }

    public function testConnectionNotFoundExceptionWithFlash()
    {
        $session = $this->getSessionWithFlash();
        $this->expectRedirect(new ConnectionNotFoundException(), 'fos_user_security_login', null, $session);
    }

    public function testUnrelatedException()
    {
        $event = $this->getEvent(new \RuntimeException());
        $this->getExceptionListener()->onKernelException($event);

        $this->assertNull($event->getResponse());
    }

    public function testSubRequest()
    {
        $listener = $this->getExceptionListener();

        $event = $this->getEvent(new NfgServiceUnavailableException(), HttpKernelInterface::SUB_REQUEST);
        $listener->onKernelException($event);

        $this->assertNull($event->getResponse());
    }

    /**
     * @return ExceptionListener
     */
    private function getExceptionListener()
    {
        $router = TestsUtil::getRouter($this);
        $translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');

        return new ExceptionListener($router, $translator);
    }

    /**
     * @return HttpKernelInterface
     */
    private function getKernel()
    {
        return $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
    }

    /**
     * @param Session $session
     * @return Request
     */
    private function getRequest($session = null)
    {
        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $request->expects($this->any())->method('getSession')->willReturn($session);

        return $request;
    }

    private function getEvent(\Exception $e, $requestType = null, $session = null)
    {
        return new GetResponseForExceptionEvent(
            $this->getKernel(),
            $this->getRequest($session),
            $requestType ? $requestType : HttpKernelInterface::MASTER_REQUEST,
            $e
        );
    }

    private function expectRedirect(\Exception $e, $route, $requestType = null, $session = null)
    {
        $event = $this->getEvent($e, $requestType, $session);
        $this->getExceptionListener()->onKernelException($event);

        /** @var RedirectResponse $response */
        $response = $event->getResponse();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertEquals($route, $response->getTargetUrl());
    }

    private function getSessionWithFlash()
    {
        $flashbag = $this->getMock('Symfony\Component\HttpFoundation\Session\Flash\FlashBag');
        $flashbag->expects($this->once())->method('add');

        $session = $this->getMock('Symfony\Component\HttpFoundation\Session\Session');
        $session->expects($this->once())->method('getFlashBag')->willReturn($flashbag);

        return $session;
    }
}
