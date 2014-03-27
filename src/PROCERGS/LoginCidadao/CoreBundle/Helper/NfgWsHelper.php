<?php
namespace PROCERGS\LoginCidadao\CoreBundle\Helper;

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

    protected $cpf;

    protected $tituloEleitor;

    protected $nome;

    protected $dataNascimento;

    protected $organizacao;

    protected $usuario;

    protected $senha;

    protected $error;

    public function setUrl($var)
    {
        $this->url = $var;
        return $this;
    }

    public function setCpf($var)
    {
        $this->cpf = $var;
        return $this;
    }

    public function setTituloEleitor($var)
    {
        $this->tituloEleitor = $var;
        return $this;
    }

    public function setNome($var)
    {
        $this->nome = $var;
        return $this;
    }

    public function setDataNascimento($var)
    {
        $this->dataNascimento = $var;
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
            if (! $this->client) {
                $this->client = new \SoapClient($this->url, array(
                    'cache_wsdl' => WSDL_CACHE_NONE,
                    'trace' => true
                ));
            }
            $result = $this->client->ConsultaCadastro(array(
                'cpf' => $this->cpf ? $this->cpf : '',
                'tituloEleitor' => $this->tituloEleitor ? $this->tituloEleitor : '',
                'nome' => $this->nome ? $this->nome : '',
                'dataNascimento' => $this->dataNascimento ? $this->dataNascimento : '',
                'organizacao' => $this->organizacao ? $this->organizacao : '',
                'usuario' => $this->usuario ? $this->usuario : '',
                'senha' => $this->senha ? $this->senha : ''
            ));
        } catch (Exception $e) {
            $this->error = $e;
            return false;
        }
        $dom = new \DOMDocument();
        if (! @$dom->loadXML($a = $result->ConsultaCadastroResult)) {
            return false;
        }
        foreach (array('CodNivelAcesso', 'CodSitRetorno', 'NomeConsumidor', 'MsgRetorno') as $val) {
            $node = $dom->getElementsByTagName($val);
            if (!$node->length) {
                return false;
            }
            $retorno[$val] = $node->item(0)->nodeValue;
        }
        return $retorno;
    }
}