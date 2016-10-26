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
        $soapService->expects($this->any())->method('getAccessID')->willReturn($accessId);

        $circuitBreaker = $this->getMock('Ejsmont\CircuitBreaker\CircuitBreakerInterface');
        $circuitBreaker->expects($this->any())->method('isAvailable')->willReturn(true);

        $router = $this->getMock('Symfony\Component\Routing\RouterInterface');
        $router->expects($this->any())->method('generate')->willReturnCallback(
            function ($routeName) {
                return $routeName;
            }
        );

        $session = $this->prophesize('Symfony\Component\HttpFoundation\Session\SessionInterface');
        $session->set(Nfg::ACCESS_ID_SESSION_KEY, $accessId)->shouldBeCalled();

        $loginEndpoint = 'https://dum.my/login';

        $nfg = new Nfg($soapService, $router, $loginEndpoint);

        $response = $nfg->login($session->reveal());
        // TODO: expect RedirectResponse when the Referrer problem at NFG gets fixed.
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertContains($accessId, $response->getContent());
        $this->assertContains('nfg_callback', $response->getContent());
    }

    public function testLoginCallback()
    {
        $cpf = '12345678901';
        $accessId = 'access_id'.random_int(10, 9999);
        $secret = "my very super secret secret";
        $prsec = hash_hmac('sha256', "$cpf$accessId", $secret);

        $session = $this->prophesize('Symfony\Component\HttpFoundation\Session\SessionInterface');
        $session->get(Nfg::ACCESS_ID_SESSION_KEY)->shouldBeCalled();

        $this->fail('not implemented yet');
    }
}
