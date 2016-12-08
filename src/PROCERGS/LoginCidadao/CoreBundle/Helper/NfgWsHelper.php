<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Helper;

use LoginCidadao\CoreBundle\Entity\Person;
use PROCERGS\LoginCidadao\CoreBundle\Exception\MissingNfgAccessTokenException;

/* sample http to test raw send

  Host: pro-pae-4729.procergs.reders
  User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:27.0) Gecko/20100101 Firefox/27.0
  Connection: keep-alive
  Content-Type: text/xml; charset=utf-8
  SOAPAction: https://m-nfg.sefaz.rs.gov.br/LoginCidadaoWs/LoginCidadaoService/ConsultaCadastro

  <?xml version="1.0" encoding="UTF-8"?>
  <s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" xmlns:u="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
  <s:Body>
  <ConsultaCadastro xmlns="https://m-nfg.sefaz.rs.gov.br/LoginCidadaoWs/">
  <cpf>02284135080</cpf>
  <tituloEleitor>053619320434</tituloEleitor>
  <nome>ivo</nome>
  <dataNascimento>11/11/2014</dataNascimento>
  <organizacao>seplag</organizacao>
  <usuario>500</usuario>
  <senha>senha1</senha>
  </ConsultaCadastro>
  </s:Body>
  </s:Envelope>
 */

class NfgWsHelper
{
    protected $url;
    protected $client;
    protected $accessToken;
    protected $tituloEleitoral;
    protected $organizacao;
    protected $usuario;
    protected $senha;
    protected $error;

    /** @var MeuRSHelper */
    protected $meuRSHelper;

    public function setUrl($var)
    {
        $this->url = $var;
        return $this;
    }

    public function setAccessToken($var)
    {
        $this->accessToken = $var;
        return $this;
    }

    public function setTituloEleitoral($var)
    {
        $this->tituloEleitoral = $var;
        return $this;
    }

    public function setOrganizacao($var)
    {
        $this->organizacao = $var;
        return $this;
    }

    public function setUsuario($var)
    {
        $this->usuario = $var;
        return $this;
    }

    public function setSenha($var)
    {
        $this->senha = $var;
        return $this;
    }

    public function getError()
    {
        return $this->error;
    }

    public function consultaCadastro()
    {
        try {
            if (!$this->client) {
                $this->client = new \SoapClient($this->url,
                    array(
                    'cache_wsdl' => WSDL_CACHE_NONE,
                    'trace' => true,
                        'stream_context' => stream_context_create(
                            [
                                'ssl' => [
                                    // disable SSL/TLS security checks
                                    'verify_peer' => false,
                                    'verify_peer_name' => false,
                                    'allow_self_signed' => true,
                                ],
                            ]
                        )
                ));
            }
            $parm = array();
            if ($this->accessToken) {
                $parm['accessToken'] = $this->accessToken;
            }
            if ($this->tituloEleitoral) {
                $parm['tituloEleitoral'] = $this->tituloEleitoral;
            }
            $parm += array(
                'organizacao' => $this->organizacao ? $this->organizacao : '',
                'usuario' => $this->usuario ? $this->usuario : '',
                'senha' => $this->senha ? $this->senha : ''
            );
            $result = $this->client->ConsultaCadastro($parm);
        } catch (Exception $e) {
            $this->error = $e;
            return false;
        }
        $dom = new \DOMDocument();
        if (!@$dom->loadXML($a   = $result->ConsultaCadastroResult)) {
            return false;
        }
        foreach (array('CodSitRetorno', 'MsgRetorno', 'CodNivelAcesso') as $val) {
            $node = $dom->getElementsByTagName($val);
            if (!$node->length) {
                return false;
            }
            $retorno[$val] = $node->item(0)->nodeValue;
        }
        if ($retorno['CodSitRetorno'] == 1 && $retorno['CodNivelAcesso'] > 1) {
            foreach (array('NomeConsumidor', 'DtNasc', 'EmailPrinc', 'NroFoneContato',
            'CodSitTitulo', 'CodCpf') as $val) {
                $node = $dom->getElementsByTagName($val);
                if (!$node->length || !strlen($node->item(0)->nodeValue)) {
                    continue;
                }
                if ($val === 'CodCpf') {
                    $retorno[$val] = str_pad($node->item(0)->nodeValue, 11, "0",
                        STR_PAD_LEFT);
                } else {
                    $retorno[$val] = $node->item(0)->nodeValue;
                }
            }
        }
        return $retorno;
    }
    /*
      POST /LoginCidadaoWS/service.svc HTTP/1.1
      Host: m-nfg-des.procergs.reders
      Connection: Keep-Alive
      User-Agent: PHP-SOAP/5.3.3
      Content-Type: text/xml; charset=utf-8
      SOAPAction: "https://m-nfg.sefaz.rs.gov.br/LoginCidadaoWs/LoginCidadaoService/ObterAccessID"
      Content-Length: 371

      <?xml version="1.0" encoding="UTF-8"?>
      <SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="https://m-nfg.sefaz.rs.gov.br/LoginCidadaoWs/">
      <SOAP-ENV:Body>
      <ns1:ObterAccessID>
      <ns1:organizacao>PROCERGS</ns1:organizacao><
      ns1:usuario>4085</ns1:usuario>
      <ns1:senha>A93SUDES</ns1:senha>
      </ns1:ObterAccessID>
      </SOAP-ENV:Body>
      </SOAP-ENV:Envelope>
     */

    public function obterAccessID()
    {
        try {
            if (!$this->client) {
                $this->client = new \SoapClient($this->url,
                    array(
                    'cache_wsdl' => WSDL_CACHE_NONE,
                    'trace' => true,
                        'stream_context' => stream_context_create(
                            [
                                'ssl' => [
                                    // disable SSL/TLS security checks
                                    'verify_peer' => false,
                                    'verify_peer_name' => false,
                                    'allow_self_signed' => true,
                                ],
                            ]
                        )
                ));
            }
            $result = $this->client->ObterAccessID(array(
                'organizacao' => $this->organizacao ? $this->organizacao : '',
                'usuario' => $this->usuario ? $this->usuario : '',
                'senha' => $this->senha ? $this->senha : ''
            ));
        } catch (Exception $e) {
            $this->error = $e;
            return false;
        }
        if (strpos($result->ObterAccessIDResult, ' ')) {
            throw new \Exception($result->ObterAccessIDResult);
        }
        return $result->ObterAccessIDResult;
    }

    public function isVoterRegistrationValid(Person $person,
                                             $voterRegistration = null)
    {
        if (is_null($voterRegistration)) {
            $voterRegistration = $this->meuRSHelper->getVoterRegistration($person);
        }

        if ($this->meuRSHelper->getNfgAccessToken()) {
            $this->setAccessToken($this->meuRSHelper->getNfgAccessToken($person));
            $this->setTituloEleitoral($voterRegistration);
            $nfgData = $this->consultaCadastro();

            // TODO: this shuldn't be here! It's NOT this method's job to validate
            // if the WebService's request was successful!
            if ($nfgData['CodSitRetorno'] != 1) {
                throw new NfgException($nfgData['MsgRetorno']);
            }

            // TODO: this shuldn't be here! It's NOT this method's job to validate
            // if the WebService's request was successful!
            if (!isset($nfgData['CodCpf'], $nfgData['NomeConsumidor'],
                    $nfgData['EmailPrinc'])) {
                throw new NfgException('nfg.missing.required.fields');
            }

            if (array_key_exists('CodSitTitulo', $nfgData) && $nfgData['CodSitTitulo']
                == 1) {
                return true;
            }
        } else {
            throw new MissingNfgAccessTokenException();
        }

        return false;
    }

    public function setMeuRSHelper(MeuRSHelper $meuRSHelper)
    {
        $this->meuRSHelper = $meuRSHelper;

        return $this;
    }
}
