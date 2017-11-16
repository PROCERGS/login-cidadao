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

use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Form\Factory\FormFactory;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Util\Canonicalizer;
use LoginCidadao\CoreBundle\Entity\Person;
use PROCERGS\LoginCidadao\NfgBundle\Entity\NfgProfile;
use PROCERGS\LoginCidadao\CoreBundle\Entity\PersonMeuRS;
use PROCERGS\LoginCidadao\NfgBundle\Exception\NfgServiceUnavailableException;
use PROCERGS\LoginCidadao\NfgBundle\Service\Nfg;
use PROCERGS\LoginCidadao\NfgBundle\Tests\TestsUtil;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;

/**
 * @codeCoverageIgnore
 */
class NfgTest extends \PHPUnit_Framework_TestCase
{
    private function getBreaker($exception = null)
    {
        $breaker = $this->getMockBuilder('Eljam\CircuitBreaker\Breaker')
            ->disableOriginalConstructor()
            ->getMock();

        if ($exception instanceof \Exception) {
            $breaker->expects($this->once())->method('protect')
                ->willThrowException($exception);
        } else {
            $breaker->expects($this->once())->method('protect')
                ->willReturnCallback(function (\Closure $closure) {
                    return $closure();
                });
        }

        return $breaker;
    }

    public function testLoginRedirectUnavailableAccessId()
    {
        $soapService = $this->getMock('\PROCERGS\LoginCidadao\NfgBundle\Service\NfgSoapInterface');
        $soapService->expects($this->once())->method('getAccessID')
            ->willThrowException(new NfgServiceUnavailableException());

        $this->setExpectedException('PROCERGS\LoginCidadao\NfgBundle\Exception\NfgServiceUnavailableException');
        $accessId = 'access_id'.random_int(10, 9999);

        $circuitBreaker = $this->getBreaker();

        /** @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject $logger */
        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $logger->expects($this->once())->method('error');

        $nfg = $this->getNfgService(
            [
                'session' => $this->getSession($accessId),
                'soap' => $soapService,
            ]
        );
        $nfg->setCircuitBreaker($circuitBreaker);
        $nfg->setLogger($logger);
        $nfg->login();
    }

    public function testLoginRedirectUnavailableUnknownError()
    {
        $soapService = $this->getMock('\PROCERGS\LoginCidadao\NfgBundle\Service\NfgSoapInterface');
        $soapService->expects($this->once())->method('getAccessID')
            ->willThrowException(new \RuntimeException());

        $this->setExpectedException('PROCERGS\LoginCidadao\NfgBundle\Exception\NfgServiceUnavailableException');
        $accessId = 'access_id'.random_int(10, 9999);

        $circuitBreaker = $this->getBreaker();

        $nfg = $this->getNfgService(
            [
                'session' => $this->getSession($accessId),
                'soap' => $soapService,
            ]
        );
        $nfg->setCircuitBreaker($circuitBreaker);
        $nfg->login();
    }

    public function testLoginRedirectUnavailable()
    {
        $this->setExpectedException('PROCERGS\LoginCidadao\NfgBundle\Exception\NfgServiceUnavailableException');
        $accessId = 'access_id'.random_int(10, 9999);

        $circuitBreaker = $this->getBreaker(new NfgServiceUnavailableException());

        $nfg = $this->getNfgService(
            [
                'session' => $this->getSession($accessId),
                'soap' => $this->getSoapService($accessId),
            ]
        );
        $nfg->setCircuitBreaker($circuitBreaker);
        $nfg->login();
    }

    public function testConnectRedirectUnavailable()
    {
        $this->setExpectedException('PROCERGS\LoginCidadao\NfgBundle\Exception\NfgServiceUnavailableException');
        $accessId = 'access_id'.random_int(10, 9999);

        $circuitBreaker = $this->getBreaker(new NfgServiceUnavailableException());

        $nfg = $this->getNfgService(
            [
                'session' => $this->getSession($accessId),
                'soap' => $this->getSoapService($accessId),
            ]
        );
        $nfg->setCircuitBreaker($circuitBreaker);
        $nfg->connect();
    }

    public function testLoginRedirect()
    {
        $accessId = 'access_id'.random_int(10, 9999);

        $circuitBreaker = $this->getBreaker();

        /** @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject $logger */
        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $logger->expects($this->once())->method('info');

        $nfg = $this->getNfgService(
            [
                'session' => $this->getSession($accessId, 'set'),
                'soap' => $this->getSoapService($accessId),
            ]
        );
        $nfg->setCircuitBreaker($circuitBreaker);
        $nfg->setLogger($logger);

        $response = $nfg->login();
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
        $personMeuRS->setPerson($person)
            ->setNfgAccessToken('dummy');
        $meuRSHelper = $this->getMeuRSHelper($cpf, $personMeuRS);

        $nfg = $this->getNfgService(
            [
                'session' => $this->getSession($accessId, 'get'),
                'soap' => $this->getSoapService($accessId),
                'meurs_helper' => $meuRSHelper,
                'login_manager' => $this->getLoginManager(true),
            ]
        );

        /** @var RedirectResponse $response */
        $response = $nfg->loginCallback(compact('cpf', 'accessId', 'prsec'), $secret);

        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertEquals('lc_home', $response->getTargetUrl());
    }

    public function testLoginCallbackMissingParams()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\BadRequestHttpException');
        $cpf = '12345678901';
        $accessId = 'access_id'.random_int(10, 9999);
        $secret = "my very super secret secret";
        $prsec = hash_hmac('sha256', "$cpf$accessId", $secret);

        $nfg = $this->getNfgService(
            [
                'session' => $this->getSession($accessId, 'none'),
                'soap' => $this->getSoapService($accessId),
            ]
        );

        $nfg->loginCallback(compact('accessId', 'prsec'), $secret);
    }

    public function testLoginCallbackInvalidSignature()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException');
        $cpf = '12345678901';
        $accessId = 'access_id'.random_int(10, 9999);
        $secret = "my very super secret secret";
        $prsec = hash_hmac('sha256', "$cpf$accessId", $secret).'_INVALID';

        $nfg = $this->getNfgService(
            [
                'session' => $this->getSession($accessId, 'none'),
                'soap' => $this->getSoapService($accessId),
            ]
        );

        $nfg->loginCallback(compact('cpf', 'accessId', 'prsec'), $secret);
    }

    public function testLoginCallbackInvalidAccessId()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException');
        $cpf = '12345678901';
        $accessId = 'access_id'.random_int(10, 9999);
        $secret = "my very super secret secret";
        $prsec = hash_hmac('sha256', "$cpf$accessId", $secret);

        $nfg = $this->getNfgService(
            [
                'session' => $this->getSession($accessId.'_INVALID', 'none'),
                'soap' => $this->getSoapService($accessId),
            ]
        );

        $nfg->loginCallback(compact('cpf', 'accessId', 'prsec'), $secret);
    }

    public function testLoginCallbackInactiveUser()
    {
        $this->setExpectedException('Symfony\Component\Security\Core\Exception\AccountStatusException');

        $cpf = '12345678901';
        $accessId = 'access_id'.random_int(10, 9999);
        $secret = "my very super secret secret";
        $prsec = hash_hmac('sha256', "$cpf$accessId", $secret);

        $person = new Person();
        $person->setCpf($cpf);
        $personMeuRS = new PersonMeuRS();
        $personMeuRS->setPerson($person)
            ->setNfgAccessToken('dummy');
        $meuRSHelper = $this->getMeuRSHelper($cpf, $personMeuRS);

        $loginManager = $this->getLoginManager();
        $loginManager->method('logInUser')->willThrowException(new AccountExpiredException());

        $nfg = $this->getNfgService(
            [
                'session' => $this->getSession($accessId, 'get'),
                'soap' => $this->getSoapService($accessId),
                'login_manager' => $loginManager,
                'meurs_helper' => $meuRSHelper,
            ]
        );

        $nfg->loginCallback(compact('cpf', 'accessId', 'prsec'), $secret);
    }

    public function testLoginNonexistentUser()
    {
        $this->setExpectedException('PROCERGS\LoginCidadao\NfgBundle\Exception\ConnectionNotFoundException');

        $cpf = '12345678901';
        $accessId = 'access_id'.random_int(10, 9999);
        $secret = "my very super secret secret";
        $prsec = hash_hmac('sha256', "$cpf$accessId", $secret);

        $person = new Person();
        $person->setCpf($cpf);
        $meuRSHelper = $this->getMeuRSHelper($cpf, null);

        $nfg = $this->getNfgService(
            [
                'session' => $this->getSession($accessId, 'get'),
                'soap' => $this->getSoapService($accessId),
                'meurs_helper' => $meuRSHelper,
                'login_manager' => $this->getLoginManager(false),
            ]
        );

        $nfg->loginCallback(compact('cpf', 'accessId', 'prsec'), $secret);
    }

    public function testPreUserInfoUnavailable()
    {
        $this->setExpectedException('PROCERGS\LoginCidadao\NfgBundle\Exception\NfgServiceUnavailableException');
        $accessId = 'access_id'.random_int(10, 9999);
        $soapService = $this->getSoapService($accessId);

        $cpf = '01234567890';
        $person = new Person();
        $person->setCpf($cpf);
        $personMeuRS = new PersonMeuRS();
        $personMeuRS
            ->setVoterRegistration('1234567890')
            ->setPerson($person);

        $circuitBreaker = $this->getBreaker(new NfgServiceUnavailableException());

        $nfg = $this->getNfgService(
            [
                'session' => $this->getSession($accessId, 'none'),
                'soap' => $soapService,
            ]
        );
        $nfg->setCircuitBreaker($circuitBreaker);

        $accessToken = 'access_token'.random_int(10, 9999);
        $request = $this->getRequest($accessToken);

        $nfg->connectCallback($request, $personMeuRS);
    }

    public function testConnectCallbackMissingAccessToken()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\BadRequestHttpException');
        $accessId = 'access_id'.random_int(10, 9999);
        $soapService = $this->getSoapService($accessId);

        $cpf = '01234567890';
        $person = new Person();
        $person->setCpf($cpf);
        $personMeuRS = new PersonMeuRS();
        $personMeuRS
            ->setVoterRegistration('1234567890')
            ->setPerson($person);

        $nfg = $this->getNfgService(
            [
                'session' => $this->getSession($accessId, 'none'),
                'soap' => $soapService,
            ]
        );

        $request = $this->getRequest(null);

        $nfg->connectCallback($request, $personMeuRS);
    }

    public function testUserInfoUnavailable()
    {
        $this->setExpectedException('PROCERGS\LoginCidadao\NfgBundle\Exception\NfgServiceUnavailableException');
        $accessId = 'access_id'.random_int(10, 9999);

        $soapService = $this->getMock('\PROCERGS\LoginCidadao\NfgBundle\Service\NfgSoapInterface');
        $soapService->expects($this->once())->method('getUserInfo')
            ->willThrowException(new NfgServiceUnavailableException());

        $cpf = '01234567890';
        $person = new Person();
        $person->setCpf($cpf);
        $personMeuRS = new PersonMeuRS();
        $personMeuRS
            ->setVoterRegistration('1234567890')
            ->setPerson($person);

        $circuitBreaker = $this->getBreaker();

        $nfg = $this->getNfgService(
            [
                'session' => $this->getSession($accessId, 'none'),
                'soap' => $soapService,
            ]
        );
        $nfg->setCircuitBreaker($circuitBreaker);

        $accessToken = 'access_token'.random_int(10, 9999);
        $request = $this->getRequest($accessToken);

        $nfg->connectCallback($request, $personMeuRS);
    }

    public function testUserInfoError()
    {
        $this->setExpectedException('PROCERGS\LoginCidadao\NfgBundle\Exception\NfgServiceUnavailableException');
        $accessId = 'access_id'.random_int(10, 9999);

        $soapService = $this->getMock('\PROCERGS\LoginCidadao\NfgBundle\Service\NfgSoapInterface');
        $soapService->expects($this->once())->method('getUserInfo')
            ->willThrowException(new \RuntimeException());

        $cpf = '01234567890';
        $person = new Person();
        $person->setCpf($cpf);
        $personMeuRS = new PersonMeuRS();
        $personMeuRS
            ->setVoterRegistration('1234567890')
            ->setPerson($person);

        $circuitBreaker = $this->getBreaker();

        $nfg = $this->getNfgService(
            [
                'session' => $this->getSession($accessId, 'none'),
                'soap' => $soapService,
            ]
        );
        $nfg->setCircuitBreaker($circuitBreaker);

        $accessToken = 'access_token'.random_int(10, 9999);
        $request = $this->getRequest($accessToken);

        $nfg->connectCallback($request, $personMeuRS);
    }

    public function testIncompleteInfo()
    {
        $this->setExpectedException('PROCERGS\LoginCidadao\NfgBundle\Exception\MissingRequiredInformationException');

        $nfgProfile = $this->getNfgProfile();
        $nfgProfile->setCpf(null)
            ->setEmail(null)
            ->setName(null)
            ->setBirthdate(null)
            ->setMobile(null);

        $accessId = 'access_id'.random_int(10, 9999);
        $soapService = $this->getSoapService($accessId);

        $cpf = '01234567890';
        $person = new Person();
        $person->setCpf($cpf);
        $personMeuRS = new PersonMeuRS();
        $personMeuRS
            ->setVoterRegistration('1234567890')
            ->setPerson($person);

        $soapService->expects($this->atLeastOnce())->method('getUserInfo')->willReturn($nfgProfile);

        $nfg = $this->getNfgService(
            [
                'session' => $this->getSession($accessId, 'none'),
                'soap' => $soapService,
            ]
        );

        $accessToken = 'access_token'.random_int(10, 9999);
        $request = $this->getRequest($accessToken);

        $nfg->connectCallback($request, $personMeuRS);
    }

    public function testMinimalInfo()
    {
        $cpf = '01234567890';
        $nfgProfile = $this->getNfgProfile();
        $nfgProfile->setCpf($cpf)
            ->setEmail('some@email.com')
            ->setName('Fulano')
            ->setBirthdate(null)
            ->setMobile(null);

        $accessId = 'access_id'.random_int(10, 9999);
        $soapService = $this->getSoapService($accessId);

        $person = new Person();
        $person->setCpf($cpf);
        $personMeuRS = new PersonMeuRS();
        $personMeuRS
            ->setVoterRegistration('1234567890')
            ->setPerson($person);

        $soapService->expects($this->atLeastOnce())->method('getUserInfo')->willReturn($nfgProfile);

        $nfg = $this->getNfgService(
            [
                'session' => $this->getSession($accessId, 'none'),
                'soap' => $soapService,
            ]
        );

        $accessToken = 'access_token'.random_int(10, 9999);
        $request = $this->getRequest($accessToken);

        $nfg->connectCallback($request, $personMeuRS);
    }

    public function testRegistration()
    {
        $accessId = 'access_id'.random_int(10, 9999);
        $soapService = $this->getSoapService($accessId);

        $nfgProfile = $this->getNfgProfile();
        $soapService->expects($this->atLeastOnce())->method('getUserInfo')->willReturn($nfgProfile);

        $meuRSHelper = $this->getMeuRSHelper($nfgProfile->getCpf(), null);

        $dispatcher = $this->getDispatcher();
        $dispatcher->expects($this->atLeastOnce())->method('dispatch')
            ->willReturnCallback(function ($eventName, $event) {
                if ($eventName === FOSUserEvents::REGISTRATION_INITIALIZE
                    && $event instanceof GetResponseUserEvent
                ) {
                    $event->setResponse(new RedirectResponse('dummy'));
                }
            });

        $nfg = $this->getNfgService(
            [
                'session' => $this->getSession($accessId, 'none'),
                'soap' => $soapService,
                'meurs_helper' => $meuRSHelper,
                'dispatcher' => $dispatcher,
            ]
        );

        $personMeuRS = new PersonMeuRS();

        $accessToken = 'access_token'.random_int(10, 9999);
        $request = $this->getRequest($accessToken);
        /** @var RedirectResponse $response */
        $response = $nfg->connectCallback($request, $personMeuRS);

        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertEquals('dummy', $response->getTargetUrl());
    }

    public function testRegistrationWithPreexistingNfgProfile()
    {
        $accessId = 'access_id'.random_int(10, 9999);
        $soapService = $this->getSoapService($accessId);

        $nfgProfile = $this->getNfgProfile();
        $soapService->expects($this->atLeastOnce())->method('getUserInfo')->willReturn($nfgProfile);

        $meuRSHelper = $this->getMeuRSHelper($nfgProfile->getCpf(), null);

        $oldNfgProfile = $this->getNfgProfile();
        $oldNfgProfile->setEmail('another@email.com');
        $nfgProfileRepo = $this->getNfgProfileRepository();
        $nfgProfileRepo->expects($this->atLeastOnce())
            ->method('findByCpf')->with($nfgProfile->getCpf())->willReturn($oldNfgProfile);

        $nfg = $this->getNfgService(
            [
                'session' => $this->getSession($accessId, 'none'),
                'soap' => $soapService,
                'meurs_helper' => $meuRSHelper,
                'nfgprofile_repository' => $nfgProfileRepo,
            ]
        );

        $personMeuRS = new PersonMeuRS();

        $accessToken = 'access_token'.random_int(10, 9999);
        $request = $this->getRequest($accessToken);
        /** @var RedirectResponse $response */
        $response = $nfg->connectCallback($request, $personMeuRS);

        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertEquals('fos_user_registration_confirmed', $response->getTargetUrl());

        $this->assertEquals($nfgProfile->getEmail(), $oldNfgProfile->getEmail());
    }

    public function testRegistrationCpfCollision()
    {
        $this->setExpectedException('PROCERGS\LoginCidadao\NfgBundle\Exception\CpfInUseException');

        $accessId = 'access_id'.random_int(10, 9999);
        $soapService = $this->getSoapService($accessId);

        $nfgProfile = $this->getNfgProfile();
        $soapService->expects($this->atLeastOnce())->method('getUserInfo')->willReturn($nfgProfile);

        $otherPerson = new Person();
        $otherPerson->setCpf($nfgProfile->getCpf());
        $otherPersonMeuRS = new PersonMeuRS();
        $otherPersonMeuRS->setPerson($otherPerson);
        $otherPersonMeuRS->setNfgAccessToken('token');
        $meuRSHelper = $this->getMeuRSHelper($nfgProfile->getCpf(), $otherPersonMeuRS);

        $nfg = $this->getNfgService(
            [
                'session' => $this->getSession($accessId, 'none'),
                'soap' => $soapService,
                'meurs_helper' => $meuRSHelper,
            ]
        );

        $accessToken = 'access_token'.random_int(10, 9999);
        $request = $this->getRequest($accessToken);
        $nfg->connectCallback($request, new PersonMeuRS());
    }

    public function testRegistrationTurnsIntoLogin()
    {
        $accessId = 'access_id'.random_int(10, 9999);
        $soapService = $this->getSoapService($accessId);

        $nfgProfile = $this->getNfgProfile();
        $soapService->expects($this->atLeastOnce())->method('getUserInfo')->willReturn($nfgProfile);

        $otherPersonMeuRS = new PersonMeuRS();
        $otherPersonMeuRS
            ->setNfgProfile($nfgProfile)
            ->setNfgAccessToken('access_token')
            ->setPerson(new Person());
        $meuRSHelper = $this->getMeuRSHelper($nfgProfile->getCpf(), $otherPersonMeuRS);

        $nfg = $this->getNfgService(
            [
                'session' => $this->getSession($accessId, 'none'),
                'soap' => $soapService,
                'meurs_helper' => $meuRSHelper,
                'login_manager' => $this->getLoginManager(true),
            ]
        );

        $accessToken = 'access_token'.random_int(10, 9999);
        $request = $this->getRequest($accessToken);
        $nfg->connectCallback($request, new PersonMeuRS());
    }

    public function testRegistrationEmailCollision()
    {
        $this->setExpectedException('PROCERGS\LoginCidadao\NfgBundle\Exception\EmailInUseException');

        $accessId = 'access_id'.random_int(10, 9999);
        $soapService = $this->getSoapService($accessId);

        $nfgProfile = $this->getNfgProfile();
        $soapService->expects($this->atLeastOnce())->method('getUserInfo')->willReturn($nfgProfile);

        $meuRSHelper = $this->getMeuRSHelper();
        $meuRSHelper->expects($this->atLeastOnce())->method('getPersonByEmail')->willReturn(new Person());

        $nfg = $this->getNfgService(
            [
                'session' => $this->getSession($accessId, 'none'),
                'soap' => $soapService,
                'meurs_helper' => $meuRSHelper,
            ]
        );

        $accessToken = 'access_token'.random_int(10, 9999);
        $request = $this->getRequest($accessToken);
        $nfg->connectCallback($request, new PersonMeuRS());
    }

    public function testLevel1Registration()
    {
        $accessId = 'access_id'.random_int(10, 9999);
        $soapService = $this->getSoapService($accessId);

        $nfgProfile = $this->getNfgProfile();
        $nfgProfile->setAccessLvl(1);
        $soapService->expects($this->atLeastOnce())->method('getUserInfo')->willReturn($nfgProfile);

        $meuRSHelper = $this->getMeuRSHelper($nfgProfile->getCpf(), null);

        $nfg = $this->getNfgService(
            [
                'session' => $this->getSession($accessId, 'none'),
                'soap' => $soapService,
                'meurs_helper' => $meuRSHelper,
            ]
        );

        $personMeuRS = new PersonMeuRS();

        $accessToken = 'access_token'.random_int(10, 9999);
        $request = $this->getRequest($accessToken);
        /** @var RedirectResponse $response */
        $response = $nfg->connectCallback($request, $personMeuRS);

        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertEquals('fos_user_registration_confirmed', $response->getTargetUrl());

        // Assert that the CPF was moved to $person
        $this->assertNotNull($personMeuRS->getNfgAccessToken());
        $this->assertNotNull($personMeuRS->getNfgProfile());
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
                'session' => $this->getSession($accessId, 'none'),
                'soap' => $soapService,
            ]
        );

        $accessToken = 'access_token'.random_int(10, 9999);
        $request = $this->getRequest($accessToken);
        /** @var RedirectResponse $response */
        $response = $nfg->connectCallback($request, $personMeuRS);

        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertEquals('fos_user_profile_edit', $response->getTargetUrl());

        // Assert that the CPF was moved to $person
        $this->assertNotNull($personMeuRS->getNfgAccessToken());
    }

    /**
     * This tests a user with CPF filled that does not match NFG's CPF
     */
    public function testConnectCallbackCpfMismatch()
    {
        $this->setExpectedException('PROCERGS\LoginCidadao\NfgBundle\Exception\CpfMismatchException');
        $accessId = 'access_id'.random_int(10, 9999);
        $soapService = $this->getSoapService($accessId);

        $cpf = '01234567891';
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
                'session' => $this->getSession($accessId, 'none'),
                'soap' => $soapService,
            ]
        );

        $accessToken = 'access_token'.random_int(10, 9999);
        $request = $this->getRequest($accessToken);
        $nfg->connectCallback($request, $personMeuRS);
    }

    /**
     * Test scenario where the person making the connection does not have a CPF in the profile but there is another
     * account that uses the user's CPF but without NFG connection.
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
            ->setId(1)
            ->setVoterRegistration('1234567890')
            ->setPerson($person);

        $nfgProfile = $this->getNfgProfile($personMeuRS->getVoterRegistration());
        $soapService->expects($this->atLeastOnce())->method('getUserInfo')->willReturn($nfgProfile);

        $otherPerson = new Person();
        $otherPerson->setCpf($cpf);
        $otherPersonMeuRS = new PersonMeuRS();
        $otherPersonMeuRS
            ->setPerson($otherPerson)
            ->setId(2);
        $meuRSHelper = $this->getMeuRSHelper($cpf, $otherPersonMeuRS);

        $nfg = $this->getNfgService(
            [
                // flush() and persist() should be called twice since we change both $person and $otherPerson
                'em' => $this->getEntityManager(['flush' => $this->exactly(2), 'persist' => $this->exactly(2)]),
                'session' => $this->getSession($accessId, 'none'),
                'soap' => $soapService,
                'meurs_helper' => $meuRSHelper,
            ]
        );

        $accessToken = 'access_token'.random_int(10, 9999);
        $request = $this->getRequest($accessToken);
        /** @var RedirectResponse $response */
        $response = $nfg->connectCallback($request, $personMeuRS);

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
        $this->setExpectedException('PROCERGS\LoginCidadao\NfgBundle\Exception\NfgAccountCollisionException');

        $accessId = 'access_id'.random_int(10, 9999);
        $accessToken = 'access_token'.random_int(10, 9999);
        $soapService = $this->getSoapService($accessId);

        $cpf = '01234567890';
        $voterRegistration = '1234567890';
        $nfgProfile = $this->getNfgProfile($voterRegistration);
        $person = new Person();
        $personMeuRS = new PersonMeuRS();
        $personMeuRS
            ->setId(1)
            ->setVoterRegistration($voterRegistration)
            ->setPerson($person);

        $soapService->expects($this->atLeastOnce())->method('getUserInfo')->willReturn($nfgProfile);

        $otherPerson = new Person();
        $otherPerson->setCpf($cpf);
        $otherPersonMeuRS = new PersonMeuRS();
        $otherPersonMeuRS
            ->setId(2)
            ->setNfgAccessToken($accessToken)
            ->setNfgProfile($nfgProfile)
            ->setPerson($otherPerson);
        $meuRSHelper = $this->getMeuRSHelper($cpf, $otherPersonMeuRS);

        $nfg = $this->getNfgService(
            [
                'session' => $this->getSession($accessId, 'none'),
                'soap' => $soapService,
                'meurs_helper' => $meuRSHelper,
            ]
        );

        $request = $this->getRequest($accessToken);
        $nfg->connectCallback($request, $personMeuRS);
    }

    /**
     * Test scenario where the person making the connection does not have a CPF in the profile but there is another
     * account that user's CPF. Also, this other account is linked to the same NFG account.
     *
     * In this scenario, the used opted to override the existing connection
     */
    public function testConnectCallbackWithoutCpfAndCollisionWithOverride()
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
            ->setId(1)
            ->setVoterRegistration($voterRegistration)
            ->setPerson($person);

        $soapService->expects($this->atLeastOnce())->method('getUserInfo')->willReturn($nfgProfile);

        $otherPerson = new Person();
        $otherPerson->setCpf($cpf);
        $otherPersonMeuRS = new PersonMeuRS();
        $otherPersonMeuRS
            ->setId(2)
            ->setNfgAccessToken($accessToken)
            ->setNfgProfile($nfgProfile)
            ->setPerson($otherPerson);
        $meuRSHelper = $this->getMeuRSHelper($cpf, $otherPersonMeuRS);

        $nfg = $this->getNfgService(
            [
                'session' => $this->getSession($accessId, 'none'),
                'soap' => $soapService,
                'meurs_helper' => $meuRSHelper,
            ]
        );

        $request = $this->getRequest($accessToken);
        $nfg->connectCallback($request, $personMeuRS, true);

        $this->assertNull($otherPersonMeuRS->getNfgProfile());
        $this->assertNull($otherPersonMeuRS->getNfgAccessToken());
    }

    public function testDisconnection()
    {
        $em = $this->getEntityManager(['flush' => $this->atLeastOnce()]);
        $em->expects($this->atLeastOnce())->method('remove')->willReturn(true);

        $person = new Person();
        $personMeuRS = new PersonMeuRS();
        $personMeuRS
            ->setNfgAccessToken('not null')
            ->setNfgProfile($this->getNfgProfile())
            ->setPerson($person);

        // Run the disconnection method
        $this
            ->getNfgService(compact('em'))
            ->disconnect($personMeuRS);

        $this->assertNull($personMeuRS->getNfgProfile());
        $this->assertNull($personMeuRS->getNfgAccessToken());
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
            $collaborators['session'] = $this->getSession($accessId, 'none');
        }
        if (false === array_key_exists('login_manager', $collaborators)) {
            $collaborators['login_manager'] = $this->getLoginManager(false);
        }
        if (false === array_key_exists('meurs_helper', $collaborators)) {
            $collaborators['meurs_helper'] = $this->getMeuRSHelper();
        }
        if (false === array_key_exists('dispatcher', $collaborators)) {
            $collaborators['dispatcher'] = $this->getDispatcher();
        }
        if (false === array_key_exists('form_factory', $collaborators)) {
            $collaborators['form_factory'] = $this->getFormFactory();
        }
        if (false === array_key_exists('user_manager', $collaborators)) {
            $collaborators['user_manager'] = $this->getUserManager();
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
        if (false === array_key_exists('nfgprofile_repository', $collaborators)) {
            $collaborators['nfgprofile_repository'] = $this->getNfgProfileRepository();
        }
        if (false === array_key_exists('mailer', $collaborators)) {
            $collaborators['mailer'] = $this->getMailer();
        }

        $canonicalizer = new Canonicalizer();

        $nfg = new Nfg(
            $collaborators['em'],
            $collaborators['soap'],
            $collaborators['router'],
            $collaborators['session'],
            $collaborators['login_manager'],
            $collaborators['meurs_helper'],
            $collaborators['dispatcher'],
            $collaborators['user_manager'],
            $collaborators['form_factory'],
            $collaborators['nfgprofile_repository'],
            $collaborators['mailer'],
            $canonicalizer,
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

    /**
     * @param bool $shouldCallLogInUser
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getLoginManager($shouldCallLogInUser = false)
    {
        if ($shouldCallLogInUser) {
            $frequency = $this->atLeastOnce();
        } else {
            $frequency = $this->any();
        }
        $loginManager = $this->getMock('\FOS\UserBundle\Security\LoginManagerInterface');
        $loginManager->expects($frequency)
            ->method('logInUser')->with(
                $this->isType('string'),
                $this->isInstanceOf('\FOS\UserBundle\Model\UserInterface'),
                $this->isInstanceOf('\Symfony\Component\HttpFoundation\Response')
            );

        return $loginManager;
    }

    /**
     * @param $accessId
     * @param null $shouldCall
     * @return \PHPUnit_Framework_MockObject_MockObject|SessionInterface
     */
    private function getSession($accessId, $shouldCall = null)
    {
        $session = $this->getMock('\Symfony\Component\HttpFoundation\Session\SessionInterface');
        switch ($shouldCall) {
            case 'get':
                $session->expects($this->atLeastOnce())
                    ->method('get')->with(Nfg::ACCESS_ID_SESSION_KEY)->willReturn($accessId);
                break;
            case 'set':
                $session->expects($this->atLeastOnce())
                    ->method('set')->with(Nfg::ACCESS_ID_SESSION_KEY, $accessId);
                break;
            default:

        }

        return $session;
    }

    private function getEntityManager(array $matchers = [])
    {
        $matchers = array_merge(
            [
                'persist' => $this->any(),
                'flush' => $this->any(),
            ],
            $matchers
        );

        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->expects($matchers['persist'])->method('persist')->willReturn(true);
        $em->expects($matchers['flush'])->method('flush')->willReturn(true);

        return $em;
    }

    /**
     * @param $accessToken
     * @return \PHPUnit_Framework_MockObject_MockObject|Request
     */
    private function getRequest($accessToken)
    {
        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $request->expects($this->atLeastOnce())->method('get')->with('paccessid')->willReturn($accessToken);

        return $request;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getDispatcher()
    {
        return $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
    }

    /**
     * @return FormFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getFormFactory()
    {
        $formFactory = $this->getMockBuilder('FOS\UserBundle\Form\Factory\FormFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $formFactory->expects($this->any())->method('createForm')
            ->willReturnCallback(function () {
                return $this->getMock('Symfony\Component\Form\FormInterface');
            });

        return $formFactory;
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

    /**
     * @return UserManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getUserManager()
    {
        $userManager = $this->getMock('FOS\UserBundle\Model\UserManagerInterface');
        $userManager->expects($this->any())->method('createUser')->willReturnCallback(function () {
            return new Person();
        });

        return $userManager;
    }

    private function getMeuRSHelper($expectedCpf = null, $response = null)
    {
        $meuRSHelper = $this->getMockBuilder('PROCERGS\LoginCidadao\CoreBundle\Helper\MeuRSHelper')
            ->disableOriginalConstructor()
            ->getMock();

        if ($expectedCpf) {
            $meuRSHelper->expects($this->atLeastOnce())
                ->method('getPersonByCpf')->with($expectedCpf)->willReturn($response);
        }

        return $meuRSHelper;
    }

    private function getNfgProfileRepository()
    {
        return $this->getMockBuilder('PROCERGS\LoginCidadao\NfgBundle\Entity\NfgProfileRepository')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getMailer()
    {
        return $this->getMock('PROCERGS\LoginCidadao\NfgBundle\Mailer\MailerInterface');
    }

    public function testRegisterCpfTakeOver()
    {
        $accessId = 'access_id'.random_int(10, 9999);
        $soapService = $this->getSoapService($accessId);

        $nfgProfile = $this->getNfgProfile();
        $soapService->expects($this->atLeastOnce())->method('getUserInfo')->willReturn($nfgProfile);

        $otherPersonMeuRS = new PersonMeuRS();
        $otherPersonMeuRS->setPerson(new Person());
        $otherPersonMeuRS->getPerson()->setCpf($nfgProfile->getCpf());
        $meuRSHelper = $this->getMeuRSHelper($nfgProfile->getCpf(), $otherPersonMeuRS);

        $dispatcher = $this->getDispatcher();
        $dispatcher->expects($this->atLeastOnce())->method('dispatch')
            ->willReturnCallback(function ($eventName, $event) {
                if ($eventName === FOSUserEvents::REGISTRATION_INITIALIZE
                    && $event instanceof GetResponseUserEvent
                ) {
                    $event->setResponse(new RedirectResponse('dummy'));
                }
            });

        $mailer = $this->getMailer();
        $mailer->expects($this->atLeastOnce())->method('notifyCpfLost');

        $nfg = $this->getNfgService(
            [
                'session' => $this->getSession($accessId, 'none'),
                'soap' => $soapService,
                'meurs_helper' => $meuRSHelper,
                'dispatcher' => $dispatcher,
                'mailer' => $mailer,
            ]
        );

        $personMeuRS = new PersonMeuRS();

        $accessToken = 'access_token'.random_int(10, 9999);
        $request = $this->getRequest($accessToken);
        /** @var RedirectResponse $response */
        $response = $nfg->connectCallback($request, $personMeuRS);

        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertEquals('dummy', $response->getTargetUrl());
    }

    public function testRegisterEmailMissing()
    {
        $this->setExpectedException('PROCERGS\LoginCidadao\NfgBundle\Exception\MissingRequiredInformationException');

        $accessId = 'access_id'.random_int(10, 9999);
        $soapService = $this->getSoapService($accessId);

        $nfgProfile = $this->getNfgProfile();
        $nfgProfile->setEmail(null);
        $soapService->expects($this->atLeastOnce())->method('getUserInfo')->willReturn($nfgProfile);

        $nfg = $this->getNfgService(
            [
                'session' => $this->getSession($accessId, 'none'),
                'soap' => $soapService,
            ]
        );

        $accessToken = 'access_token'.random_int(10, 9999);
        $request = $this->getRequest($accessToken);
        $nfg->connectCallback($request, new PersonMeuRS());
    }
}
