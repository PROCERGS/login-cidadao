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
use PROCERGS\LoginCidadao\CoreBundle\Entity\NfgProfile;
use PROCERGS\LoginCidadao\CoreBundle\Entity\PersonMeuRS;
use PROCERGS\LoginCidadao\NfgBundle\Exception\NfgAccountCollisionException;
use PROCERGS\LoginCidadao\NfgBundle\Service\Nfg;
use PROCERGS\LoginCidadao\NfgBundle\Tests\TestsUtil;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class NfgTest extends \PHPUnit_Framework_TestCase
{
    public function testLoginRedirect()
    {
        $accessId = 'access_id'.random_int(10, 9999);

        $cbService = 'service';
        $circuitBreaker = $this->getCircuitBreaker($cbService, true);
        $circuitBreaker->reportSuccess($cbService)->shouldBeCalled();

        $nfg = $this->getNfgService(
            [
                'session' => $this->getSession($accessId, 'set')->reveal(),
                'soap' => $this->getSoapService($accessId),
            ]
        );
        $nfg->setCircuitBreaker($circuitBreaker->reveal(), $cbService);

        $response = $nfg->login();
        // TODO: expect RedirectResponse once the Referrer problem at NFG gets fixed.
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

        $nfg = $this->getNfgService(
            [
                'session' => $this->getSession($accessId, 'get')->reveal(),
                'soap' => $this->getSoapService($accessId),
                'meurs_helper' => $meuRSHelper->reveal(),
                'login_manager' => $this->getLoginManager(true)->reveal(),
            ]
        );

        $response = $nfg->loginCallback(compact('cpf', 'accessId', 'prsec'), $secret);

        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertEquals('lc_home', $response->getTargetUrl());
    }

    /**
     * This tests a user with CPF filled and with no CPF collision
     */
    public function testConnectCallbackWithCpf()
    {
        $accessId = 'access_id'.random_int(10, 9999);
        $soapService = $this->getSoapService($accessId);

        $cpf = '01234567890';
        $person = new Person();
        $person->setCpf($cpf);
        $personMeuRS = new PersonMeuRS();
        $personMeuRS
            ->setVoterRegistration('1234567890')
            ->setPerson($person);

        $nfgProfile = $this->getNfgProfile($personMeuRS->getVoterRegistration());
        $soapService->expects($this->atLeastOnce())->method('getUserInfo')->willReturn($nfgProfile);

        $nfg = $this->getNfgService(
            [
                'session' => $this->getSession($accessId, 'none')->reveal(),
                'soap' => $soapService,
            ]
        );

        $accessToken = 'access_token'.random_int(10, 9999);
        $response = $nfg->connectCallback($personMeuRS, $accessToken);

        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertEquals('fos_user_profile_edit', $response->getTargetUrl());

        // Assert that the CPF was moved to $person
        $this->assertNotNull($personMeuRS->getNfgAccessToken());
    }

    /**
     * Test scenario where the person making the connection does not have a CPF in the profile but there is another
     * account that user's CPF but without NFG connection.
     *
     * The other user's account should have the CPF set to NULL and the current user will be connected to NFG
     */
    public function testConnectCallbackWithoutCpfAndSimpleCollision()
    {
        $accessId = 'access_id'.random_int(10, 9999);
        $soapService = $this->getSoapService($accessId);

        $cpf = '01234567890';
        $person = new Person();
        $personMeuRS = new PersonMeuRS();
        $personMeuRS
            ->setVoterRegistration('1234567890')
            ->setPerson($person);

        $nfgProfile = $this->getNfgProfile($personMeuRS->getVoterRegistration());
        $soapService->expects($this->atLeastOnce())->method('getUserInfo')->willReturn($nfgProfile);

        $otherPerson = new Person();
        $otherPerson->setCpf($cpf);
        $otherPersonMeuRS = new PersonMeuRS();
        $otherPersonMeuRS
            ->setPerson($otherPerson);
        $meuRSHelper = $this->prophesize('PROCERGS\LoginCidadao\CoreBundle\Helper\MeuRSHelper');
        $meuRSHelper->getPersonByCpf($cpf)->willReturn($otherPersonMeuRS)->shouldBeCalled();

        $nfg = $this->getNfgService(
            [
                'session' => $this->getSession($accessId, 'none')->reveal(),
                'soap' => $soapService,
                'meurs_helper' => $meuRSHelper->reveal(),
            ]
        );

        $accessToken = 'access_token'.random_int(10, 9999);
        $response = $nfg->connectCallback($personMeuRS, $accessToken);

        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertEquals('fos_user_profile_edit', $response->getTargetUrl());

        // Assert the connection to NFG was made
        $this->assertNotNull($personMeuRS->getNfgAccessToken());

        // Assert that the CPF was moved to $person
        $this->assertNull($otherPerson->getCpf());
        $this->assertEquals($cpf, $person->getCpf());
    }

    /**
     * Test scenario where the person making the connection does not have a CPF in the profile but there is another
     * account that user's CPF. Also, this other account is linked to the same NFG account.
     */
    public function testConnectCallbackWithoutCpfAndCollision()
    {
        $accessId = 'access_id'.random_int(10, 9999);
        $accessToken = 'access_token'.random_int(10, 9999);
        $soapService = $this->getSoapService($accessId);

        $cpf = '01234567890';
        $voterRegistration = '1234567890';
        $nfgProfile = $this->getNfgProfile($voterRegistration);
        $person = new Person();
        $personMeuRS = new PersonMeuRS();
        $personMeuRS
            ->setVoterRegistration($voterRegistration)
            ->setPerson($person);

        $soapService->expects($this->atLeastOnce())->method('getUserInfo')->willReturn($nfgProfile);

        $otherPerson = new Person();
        $otherPerson->setCpf($cpf);
        $otherPersonMeuRS = new PersonMeuRS();
        $otherPersonMeuRS
            ->setNfgAccessToken($accessToken)
            ->setNfgProfile($nfgProfile)
            ->setPerson($otherPerson);
        $meuRSHelper = $this->prophesize('PROCERGS\LoginCidadao\CoreBundle\Helper\MeuRSHelper');
        $meuRSHelper->getPersonByCpf($cpf)->willReturn($otherPersonMeuRS)->shouldBeCalled();

        $nfg = $this->getNfgService(
            [
                'session' => $this->getSession($accessId, 'none')->reveal(),
                'soap' => $soapService,
                'meurs_helper' => $meuRSHelper->reveal(),
            ]
        );

        try {
            $nfg->connectCallback($personMeuRS, $accessToken);
            $this->fail('Exception was not thrown!');
        } catch (NfgAccountCollisionException $e) {
            $this->assertInstanceOf('PROCERGS\LoginCidadao\NfgBundle\Exception\NfgAccountCollisionException', $e);
        }
    }

    private function getNfgService(array $collaborators)
    {
        $accessId = 'access_id'.random_int(10, 9999);
        if (false === array_key_exists('em', $collaborators)) {
            $collaborators['em'] = $this->getEntityManager();
        }
        if (false === array_key_exists('soap', $collaborators)) {
            $collaborators['soap'] = $this->getSoapService($accessId);
        }
        if (false === array_key_exists('router', $collaborators)) {
            $collaborators['router'] = TestsUtil::getRouter($this);
        }
        if (false === array_key_exists('session', $collaborators)) {
            $collaborators['session'] = $this->getSession($accessId, 'none')->reveal();
        }
        if (false === array_key_exists('login_manager', $collaborators)) {
            $collaborators['login_manager'] = $this->getLoginManager(false)->reveal();
        }
        if (false === array_key_exists('meurs_helper', $collaborators)) {
            $collaborators['meurs_helper'] = $meuRSHelper = $this->prophesize(
                'PROCERGS\LoginCidadao\CoreBundle\Helper\MeuRSHelper'
            )->reveal();
        }
        if (false === array_key_exists('firewall', $collaborators)) {
            $collaborators['firewall'] = 'firewall';
        }
        if (false === array_key_exists('login_endpoint', $collaborators)) {
            $collaborators['login_endpoint'] = 'https://dum.my/login';
        }
        if (false === array_key_exists('auth_endpoint', $collaborators)) {
            $collaborators['auth_endpoint'] = 'https://dum.my/auth';
        }

        $nfg = new Nfg(
            $collaborators['em'],
            $collaborators['soap'],
            $collaborators['router'],
            $collaborators['session'],
            $collaborators['login_manager'],
            $collaborators['meurs_helper'],
            $collaborators['firewall'],
            $collaborators['login_endpoint'],
            $collaborators['auth_endpoint']
        );

        return $nfg;
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

    /**
     * @param $accessId
     * @param null $shouldCall
     * @return SessionInterface
     */
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

    private function getEntityManager()
    {
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->expects($this->any())->method('persist')->willReturn(true);
        $em->expects($this->any())->method('flush')->willReturn(true);

        return $em;
    }

    private function getNfgProfile($voterRegistration = null)
    {
        $nfgProfile = new NfgProfile();
        $nfgProfile->setName('John Doe')
            ->setEmail('some@email.com')
            ->setBirthdate('1970-01-01T00:00:00')
            ->setMobile('+555193333333')
            ->setVoterRegistration($voterRegistration)
            ->setCpf('1234567890')// NFG treats CPF as integers, hence no leading 0s
            ->setAccessLvl(2);

        return $nfgProfile;
    }
}
