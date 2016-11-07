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
use PROCERGS\LoginCidadao\NfgBundle\Exception\CpfMismatchException;
use PROCERGS\LoginCidadao\NfgBundle\Exception\NfgServiceUnavailableException;
use PROCERGS\LoginCidadao\NfgBundle\Tests\TestsUtil;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ExceptionListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testServiceUnavailable()
    {
        $this->expectRedirect(new NfgServiceUnavailableException(), 'nfg_unavailable');
    }

    public function testCpfMismatch()
    {
        $this->expectRedirect(new CpfMismatchException(), 'lc_documents');
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

        return new ExceptionListener($router);
    }

    /**
     * @return HttpKernelInterface
     */
    private function getKernel()
    {
        return $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
    }

    /**
     * @return Request
     */
    private function getRequest()
    {
        return $this->getMock('Symfony\Component\HttpFoundation\Request');
    }

    private function getEvent(\Exception $e, $requestType = null)
    {
        return new GetResponseForExceptionEvent(
            $this->getKernel(),
            $this->getRequest(),
            $requestType ? $requestType : HttpKernelInterface::MASTER_REQUEST,
            $e
        );
    }

    private function expectRedirect(\Exception $e, $route, $requestType = null)
    {
        $event = $this->getEvent($e, $requestType);
        $this->getExceptionListener()->onKernelException($event);

        /** @var RedirectResponse $response */
        $response = $event->getResponse();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertEquals($route, $response->getTargetUrl());
    }
}
