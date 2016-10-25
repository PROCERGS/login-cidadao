<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\NfgBundle\Tests\Service;


use PROCERGS\LoginCidadao\NfgBundle\Service\Nfg;

class NfgTest extends \PHPUnit_Framework_TestCase
{
    public function testLoginRedirect()
    {
        $accessId = 'access_id'.random_int(10, 9999);
        $soapService = $this->getMock('PROCERGS\LoginCidadao\NfgBundle\Service\NfgSoapInterface');
        $soapService->expects($this->any())->method('obterAccessID')->willReturn($accessId);

        $circuitBreaker = $this->getMock('Ejsmont\CircuitBreaker\CircuitBreakerInterface');
        $circuitBreaker->expects($this->any())->method('isAvailable')->willReturn(true);

        $router = $this->getMock('Symfony\Component\Routing\RouterInterface');
        $router->expects($this->any())->method('generate')->willReturnCallback(
            function ($routeName) {
                return $routeName;
            }
        );

        $loginEndpoint = 'https://dum.my/login';

        $nfg = new Nfg($soapService, $router, $loginEndpoint);

        $response = $nfg->login();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertContains($accessId, $response->getTargetUrl());
        $this->assertContains('nfg_callback', $response->getTargetUrl());
    }
}
