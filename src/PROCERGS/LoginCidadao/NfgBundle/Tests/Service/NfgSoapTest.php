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

use PROCERGS\LoginCidadao\NfgBundle\Exception\NfgServiceUnavailableException;
use PROCERGS\LoginCidadao\NfgBundle\Security\Credentials;
use PROCERGS\LoginCidadao\NfgBundle\Service\NfgSoap;

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
        $client = $this->getSoapClientMock();
        $credentials = new Credentials('invalid', 'invalid', 'invalid');
        $nfgSoap = new NfgSoap($client, $credentials);

        try {
            $nfgSoap->getAccessID();
            $this->fail('An exception was expected when invalid credentials are given.');
        } catch (NfgServiceUnavailableException $e) {
            $this->assertEquals('error ', $e->getMessage());
        }
    }

    public function testGetFullUserInfo()
    {
        $cpf = '5745778407';
        $name = 'John Doe';
        $birthday = '1970-01-01T00:00:00';
        $email = 'john@doe.test';
        $phone = '5192345678';
        $voterRegistration = '1234';
        $cod_sit_voter_registration = '1';

        $client = $this->getSoapClientMock(
            compact('cpf', 'name', 'birthday', 'email', 'phone', 'cod_sit_voter_registration')
        );
        $credentials = new Credentials('org', 'user', 'pass');
        $nfgSoap = new NfgSoap($client, $credentials);

        $nfgProfile = $nfgSoap->getUserInfo('stub', $voterRegistration);

        $this->assertEquals($name, $nfgProfile->getName());
        $this->assertEquals($email, $nfgProfile->getEmail());
        $this->assertEquals($birthday, $nfgProfile->getBirthdate());
        $this->assertNotNull($nfgProfile->getMobile());
        $this->assertEquals("+55$phone", $nfgProfile->getMobile());
        $this->assertEquals($cod_sit_voter_registration, $nfgProfile->getVoterRegistrationSit());
        $this->assertEquals($voterRegistration, $nfgProfile->getVoterRegistration());
    }

    public function testNotMobilePhone()
    {
        $client = $this->getSoapClientMock(['phone' => '5133333333']);
        $credentials = new Credentials('org', 'user', 'pass');
        $nfgSoap = new NfgSoap($client, $credentials);

        $nfgProfile = $nfgSoap->getUserInfo('stub');

        $this->assertNull($nfgProfile->getMobile());
    }

    private function getSoapClientMock(array $info = [])
    {
        if (!array_key_exists('cpf', $info)) {
            $info['cpf'] = '5745778407';
        }
        if (!array_key_exists('name', $info)) {
            $info['name'] = 'John Doe';
        }
        if (!array_key_exists('birthday', $info)) {
            $info['birthday'] = '1970-01-01T00:00:00';
        }
        if (!array_key_exists('email', $info)) {
            $info['email'] = 'john@doe.test';
        }
        if (!array_key_exists('phone', $info)) {
            $info['phone'] = '5192345678';
        }
        if (!array_key_exists('cod_sit_voter_registration', $info)) {
            $info['cod_sit_voter_registration'] = '0';
        }

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
            ->willReturnCallback(
                function ($data) {
                    if ($data['organizacao'] === 'org'
                        && $data['usuario'] === 'user'
                        && $data['senha'] === 'pass'
                    ) {
                        $response = '{"ObterAccessIDResult":"ok"}';
                    } else {
                        $response = '{"ObterAccessIDResult":"error "}';
                    }

                    return json_decode($response);
                }
            );
        $client->expects($this->any())
            ->method('ConsultaCadastro')
            ->willReturnCallback(
                function ($data) use ($info) {
                    $xml = <<<XML
<?xml version="1.0"?>
<LoginCidadaoServiceED xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
  <CodSitRetorno>1</CodSitRetorno>
  <CodNivelAcesso>2</CodNivelAcesso>
  <CodCpf>{$info['cpf']}</CodCpf>
  <NomeConsumidor>{$info['name']}</NomeConsumidor>
  <DtNasc>{$info['birthday']}</DtNasc>
  <EmailPrinc>{$info['email']}</EmailPrinc>
  <NroFoneContato>{$info['phone']}</NroFoneContato>
  <CodSitTitulo>{$info['cod_sit_voter_registration']}</CodSitTitulo>
  <MsgRetorno>Sucesso.</MsgRetorno>
</LoginCidadaoServiceED>
XML;
                    $response = new \stdClass();
                    $response->ConsultaCadastroResult = $xml;

                    return $response;
                }
            );

        return $client;
    }
}
