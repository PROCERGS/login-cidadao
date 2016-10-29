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
        $client = $this->getSoapClientMock();
        $credentials = new Credentials('org', 'user', 'pass');
        $nfgSoap = new NfgSoap($client, $credentials);

        $nfgProfile = $nfgSoap->getUserInfo('stub');

        $this->assertContains('Donato', $nfgProfile->getName());
        $this->assertNotNull($nfgProfile->getMobile());
        $this->assertStringStartsWith('+55', $nfgProfile->getMobile());
    }

    private function getSoapClientMock()
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
                function ($data) {
                    $xml = <<<XML
<?xml version="1.0"?>
<LoginCidadaoServiceED xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
  <CodSitRetorno>1</CodSitRetorno>
  <CodNivelAcesso>2</CodNivelAcesso>
  <CodCpf>2197368044</CodCpf>
  <NomeConsumidor>Guilherme Da Silva Donato</NomeConsumidor>
  <DtNasc>1989-08-21T00:00:00</DtNasc>
  <EmailPrinc>nfg@cadastro.dona.to</EmailPrinc>
  <NroFoneContato>5193313045</NroFoneContato>
  <CodSitTitulo>0</CodSitTitulo>
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
