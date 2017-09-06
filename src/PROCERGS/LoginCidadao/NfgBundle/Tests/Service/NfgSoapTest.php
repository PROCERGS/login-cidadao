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

use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use PROCERGS\LoginCidadao\NfgBundle\Security\Credentials;
use PROCERGS\LoginCidadao\NfgBundle\Service\NfgSoap;

/**
 * @codeCoverageIgnore
 */
class NfgSoapTest extends \PHPUnit_Framework_TestCase
{
    public function testValidGetAccessID()
    {
        $client = $this->getSoapClientMock();
        $credentials = new Credentials('org', 'user', 'pass');
        $nfgSoap = new NfgSoap($client, $credentials);

        $accessId = $nfgSoap->getAccessID();
        $this->assertEquals('ok', $accessId);
    }

    public function testInvalidCredentialsGetAccessID()
    {
        $this->setExpectedException('PROCERGS\LoginCidadao\NfgBundle\Exception\NfgServiceUnavailableException');

        $client = $this->getSoapClientMock();
        $credentials = new Credentials('invalid', 'invalid', 'invalid');
        $nfgSoap = new NfgSoap($client, $credentials);
        $nfgSoap->getAccessID();
    }

    public function testGetFullUserInfo()
    {
        $voterRegistration = '1234';
        $userInfo = [
            'CodCpf' => '5745778407',
            'NomeConsumidor' => 'John Doe',
            'DtNasc' => '1970-01-01T00:00:00',
            'EmailPrinc' => 'john@doe.test',
            'NroFoneContato' => '51992345678',
            'CodSitTitulo' => '1',
        ];

        $client = $this->getSoapClientMock($userInfo);
        $credentials = new Credentials('org', 'user', 'pass');
        $nfgSoap = new NfgSoap($client, $credentials);

        $nfgProfile = $nfgSoap->getUserInfo('stub', $voterRegistration);

        $phoneUtil = PhoneNumberUtil::getInstance();
        $this->assertEquals($userInfo['NomeConsumidor'], $nfgProfile->getName());
        $this->assertEquals($userInfo['EmailPrinc'], $nfgProfile->getEmail());
        $this->assertInstanceOf('\DateTime', $nfgProfile->getBirthdate());
        $this->assertEquals($userInfo['DtNasc'], $nfgProfile->getBirthdate()->format('Y-m-d\TH:i:s'));
        $this->assertNotNull($nfgProfile->getMobile());
        $this->assertEquals(
            "+55{$userInfo['NroFoneContato']}",
            $phoneUtil->format($nfgProfile->getMobile(), PhoneNumberFormat::E164)
        );
        $this->assertEquals($userInfo['CodSitTitulo'], $nfgProfile->getVoterRegistrationSit());
        $this->assertEquals($voterRegistration, $nfgProfile->getVoterRegistration());
    }

    public function testInvalidPhone()
    {
        $voterRegistration = '1234';
        $userInfo = [
            'CodCpf' => '5745778407',
            'NomeConsumidor' => 'John Doe',
            'DtNasc' => '1970-01-01T00:00:00',
            'EmailPrinc' => 'john@doe.test',
            'NroFoneContato' => 'aa',
            'CodSitTitulo' => '1',
        ];

        $client = $this->getSoapClientMock($userInfo);
        $credentials = new Credentials('org', 'user', 'pass');
        $nfgSoap = new NfgSoap($client, $credentials);

        $nfgProfile = $nfgSoap->getUserInfo('stub', $voterRegistration);

        $this->assertEquals($userInfo['NomeConsumidor'], $nfgProfile->getName());
        $this->assertEquals($userInfo['EmailPrinc'], $nfgProfile->getEmail());
        $this->assertInstanceOf('\DateTime', $nfgProfile->getBirthdate());
        $this->assertEquals($userInfo['DtNasc'], $nfgProfile->getBirthdate()->format('Y-m-d\TH:i:s'));
        $this->assertNull($nfgProfile->getMobile());
        $this->assertEquals($userInfo['CodSitTitulo'], $nfgProfile->getVoterRegistrationSit());
        $this->assertEquals($voterRegistration, $nfgProfile->getVoterRegistration());
    }

    public function testErrorResponse()
    {
        $this->setExpectedException('RuntimeException');
        $userInfo = [
            'CodSitRetorno' => '500',
            'MsgRetorno' => 'Some error',
        ];

        $client = $this->getSoapClientMock($userInfo);
        $credentials = new Credentials('org', 'user', 'pass');
        $nfgSoap = new NfgSoap($client, $credentials);

        $nfgSoap->getUserInfo('stub');
    }

    public function testPhoneMissing()
    {
        $voterRegistration = '1234';
        $userInfo = [
            'CodCpf' => '5745778407',
            'NomeConsumidor' => 'John Doe',
            'DtNasc' => '1970-01-01T00:00:00',
            'EmailPrinc' => 'john@doe.test',
            'NroFoneContato' => null,
            'CodSitTitulo' => '1',
        ];

        $client = $this->getSoapClientMock($userInfo);
        $credentials = new Credentials('org', 'user', 'pass');
        $nfgSoap = new NfgSoap($client, $credentials);

        $nfgProfile = $nfgSoap->getUserInfo('stub', $voterRegistration);

        $this->assertEquals($userInfo['NomeConsumidor'], $nfgProfile->getName());
        $this->assertEquals($userInfo['EmailPrinc'], $nfgProfile->getEmail());
        $this->assertInstanceOf('\DateTime', $nfgProfile->getBirthdate());
        $this->assertEquals($userInfo['DtNasc'], $nfgProfile->getBirthdate()->format('Y-m-d\TH:i:s'));
        $this->assertNull($nfgProfile->getMobile());
        $this->assertEquals($userInfo['CodSitTitulo'], $nfgProfile->getVoterRegistrationSit());
        $this->assertEquals($voterRegistration, $nfgProfile->getVoterRegistration());
    }

    public function testMinimalInfo()
    {
        $userInfo = [
            'CodCpf' => true,
            'NomeConsumidor' => null,
            'DtNasc' => null,
            'EmailPrinc' => null,
            'NroFoneContato' => null,
            'CodSitTitulo' => '0',
        ];

        $client = $this->getSoapClientMock($userInfo);
        $credentials = new Credentials('org', 'user', 'pass');
        $nfgSoap = new NfgSoap($client, $credentials);

        $nfgProfile = $nfgSoap->getUserInfo('stub');

        $this->assertNull($nfgProfile->getName());
        $this->assertNull($nfgProfile->getEmail());
        $this->assertNull($nfgProfile->getBirthdate());
        $this->assertNull($nfgProfile->getMobile());
        $this->assertEquals($userInfo['CodSitTitulo'], $nfgProfile->getVoterRegistrationSit());
    }

    public function testNotMobilePhone()
    {
        $client = $this->getSoapClientMock(['NroFoneContato' => '5133333333']);
        $credentials = new Credentials('org', 'user', 'pass');
        $nfgSoap = new NfgSoap($client, $credentials);

        $nfgProfile = $nfgSoap->getUserInfo('stub');

        $this->assertNull($nfgProfile->getMobile());
    }

    public function testOldMobilePhone()
    {
        $client = $this->getSoapClientMock(['NroFoneContato' => '5188888888']);
        $credentials = new Credentials('org', 'user', 'pass');
        $nfgSoap = new NfgSoap($client, $credentials);

        $nfgProfile = $nfgSoap->getUserInfo('stub');

        $this->assertEquals('51988888888', $nfgProfile->getMobile()->getNationalNumber());
    }

    /**
     * @param array $info
     * @return \PHPUnit_Framework_MockObject_MockObject|\SoapClient
     */
    private function getSoapClientMock(array $info = [])
    {
        $client = $this->getMock(
            '\SoapClient',
            ['ObterAccessID', 'ConsultaCadastro'],
            ['https://dum.my/service.wsdl'],
            '',
            false,
            false,
            false,
            true,
            false
        );
        $client->expects($this->any())
            ->method('ObterAccessID')
            ->willReturnCallback(function ($data) {
                if ($data['organizacao'] === 'org'
                    && $data['usuario'] === 'user'
                    && $data['senha'] === 'pass'
                ) {
                    $response = '{"ObterAccessIDResult":"ok"}';
                } else {
                    $response = '{"ObterAccessIDResult":"error "}';
                }

                return json_decode($response);
            });

        $xml = $this->getUserInfoXmlResponse($info);
        $client->expects($this->any())
            ->method('ConsultaCadastro')
            ->willReturnCallback(function () use ($xml) {
                $response = new \stdClass();
                $response->ConsultaCadastroResult = $xml;

                return $response;
            });

        return $client;
    }

    /**
     * @param array $info expected keys are:
     *      CodSitRetorno
     *      CodNivelAcesso
     *      CodCpf
     *      NomeConsumidor
     *      DtNasc
     *      EmailPrinc
     *      NroFoneContato
     *      CodSitTitulo
     *      MsgRetorno
     * @return string
     */
    private function getUserInfoXmlResponse($info)
    {
        $default = [
            'CodSitRetorno' => '1',
            'CodNivelAcesso' => '2',
            'CodCpf' => '5745778407',
            'NomeConsumidor' => 'John Doe',
            'DtNasc' => '1970-01-01T00:00:00',
            'EmailPrinc' => 'john@doe.test',
            'NroFoneContato' => '51992345678',
            'CodSitTitulo' => '0',
            'MsgRetorno' => 'Sucesso.',
        ];
        $info = array_filter(
            array_merge($default, $info),
            function ($value) {
                return $value !== null;
            }
        );

        $xml = '<?xml version="1.0"?><LoginCidadaoServiceED xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">';

        foreach ($info as $key => $value) {
            $xml .= "<$key>$value</$key>";
        }
        $xml .= '</LoginCidadaoServiceED>';

        return $xml;
    }
}
