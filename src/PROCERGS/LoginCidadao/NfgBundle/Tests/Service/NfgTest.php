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


use LoginCidadao\CoreBundle\Entity\Person;
use PROCERGS\LoginCidadao\CoreBundle\Entity\PersonMeuRS;
use PROCERGS\LoginCidadao\NfgBundle\Service\Nfg;
use Prophecy\Argument;

class NfgTest extends \PHPUnit_Framework_TestCase
{
    public function testLoginRedirect()
    {
        $accessId = 'access_id'.random_int(10, 9999);
        $soapService = $this->getSoapService($accessId);

        $cbService = 'service';
        $circuitBreaker = $this->getCircuitBreaker($cbService, true);
        $circuitBreaker->reportSuccess($cbService)->shouldBeCalled();

        $meuRSHelper = $this->getMockBuilder('PROCERGS\LoginCidadao\CoreBundle\Helper\MeuRSHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $session = $this->getSession($accessId, 'set');
        $loginManager = $this->getLoginManager();
        $loginEndpoint = 'https://dum.my/login';
        $authEndpoint = 'https://dum.my/auth';
        $firewall = 'firewall';

        $nfg = new Nfg(
            $soapService,
            $this->getRouter(),
            $session->reveal(),
            $loginManager->reveal(),
            $meuRSHelper,
            $firewall,
            $loginEndpoint,
            $authEndpoint
        );
        $nfg->setCircuitBreaker($circuitBreaker->reveal(), $cbService);

        $response = $nfg->login();
        // TODO: expect RedirectResponse when the Referrer problem at NFG gets fixed.
        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $response);
        $this->assertContains($accessId, $response->getContent());
        $this->assertContains('nfg_login_callback', $response->getContent());
    }

    public function testLoginCallback()
    {
        $cpf = '12345678901';
        $accessId = 'access_id'.random_int(10, 9999);
        $secret = "my very super secret secret";
        $prsec = hash_hmac('sha256', "$cpf$accessId", $secret);

        $person = new Person();
        $person->setCpf($cpf);
        $personMeuRS = new PersonMeuRS();
        $personMeuRS->setPerson($person);
        $meuRSHelper = $this->prophesize('PROCERGS\LoginCidadao\CoreBundle\Helper\MeuRSHelper');
        $meuRSHelper->getPersonByCpf($cpf)->willReturn($personMeuRS)->shouldBeCalled();

        $session = $this->getSession($accessId, 'get');

        $firewall = 'firewall';
        $loginManager = $this->getLoginManager(true);
        $loginEndpoint = 'https://dum.my/login';
        $authEndpoint = 'https://dum.my/auth';

        $nfg = new Nfg(
            $this->getSoapService($accessId),
            $this->getRouter(),
            $session->reveal(),
            $loginManager->reveal(),
            $meuRSHelper->reveal(),
            $firewall,
            $loginEndpoint,
            $authEndpoint
        );

        $response = $nfg->loginCallback(compact('cpf', 'accessId', 'prsec'), $secret);

        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertEquals('lc_home', $response->getTargetUrl());
    }

    private function getRouter()
    {
        $router = $this->getMock('\Symfony\Component\Routing\RouterInterface');
        $router->expects($this->any())->method('generate')->willReturnCallback(
            function ($routeName) {
                return $routeName;
            }
        );

        return $router;
    }

    private function getSoapService($accessId)
    {
        $soapService = $this->getMock('\PROCERGS\LoginCidadao\NfgBundle\Service\NfgSoapInterface');
        $soapService->expects($this->any())->method('getAccessID')->willReturn($accessId);

        return $soapService;
    }

    private function getLoginManager($shouldCallLogInUser = false)
    {
        $loginManager = $this->prophesize('\FOS\UserBundle\Security\LoginManagerInterface');
        $logInUser = $loginManager->logInUser(
            Argument::type('string'),
            Argument::type('\FOS\UserBundle\Model\UserInterface'),
            Argument::type('\Symfony\Component\HttpFoundation\Response')
        );
        if ($shouldCallLogInUser) {
            $logInUser->shouldBeCalled();
        }

        return $loginManager;
    }

    private function getSession($accessId, $shouldCall = null)
    {
        $session = $this->prophesize('\Symfony\Component\HttpFoundation\Session\SessionInterface');
        switch ($shouldCall) {
            case 'get':
                $session->get(Nfg::ACCESS_ID_SESSION_KEY)->willReturn($accessId)->shouldBeCalled();
                break;
            case 'set':
                $session->set(Nfg::ACCESS_ID_SESSION_KEY, $accessId)->shouldBeCalled();
                break;
            default:

        }

        return $session;
    }

    /**
     * @param string $serviceName
     * @param bool $isAvailable
     * @return \Prophecy\Prophecy\ObjectProphecy
     */
    private function getCircuitBreaker($serviceName, $isAvailable)
    {
        $circuitBreaker = $this->prophesize('\Ejsmont\CircuitBreaker\CircuitBreakerInterface');
        $circuitBreaker->isAvailable($serviceName)->willReturn($isAvailable)->shouldBeCalled();

        return $circuitBreaker;
    }
}
