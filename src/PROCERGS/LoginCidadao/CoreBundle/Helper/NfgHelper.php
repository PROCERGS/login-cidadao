<?php
namespace PROCERGS\LoginCidadao\CoreBundle\Helper;

class NfgHelper
{

    protected $url = 'https://nfg.sefaz.rs.gov.br';

    protected $services = array(
        'handshake' => '/Login/LoginNfg.aspx',
        'login' => '/Login/LoginNfgDo.aspx',
        'profile' => '/Cadastro/CadastroNfgMan.aspx'
    );

    protected $ch;

    protected $cookie;

    protected $username;

    protected $password;

    protected $isLoggedIn;

    protected function handShake()
    {
        $this->_common();
        // curl_setopt($this->ch, CURLOPT_NOBODY, 1);
        curl_setopt($this->ch, CURLOPT_URL, $this->url . $this->services['handshake']);
        $result = curl_exec($this->ch);
        curl_close($this->ch);
        return $result;
    }

    private function _common($header = array())
    {
        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_HEADER, 1);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
        if (ini_get('open_basedir')) {
            
        } else {
            curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
        }
        $headApp = array(
            'Content-Type: application/x-www-form-urlencoded',
            'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:27.0) Gecko/20100101 Firefox/27.0',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: pt-BR,pt;q=0.8,en-US;q=0.5,en;q=0.3',            
            'Connection: keep-alive',
            'Host: nfg.sefaz.rs.gov.br'
        );
        $headApp = array_merge($headApp, $header);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headApp);
        curl_setopt($this->ch, CURLOPT_ENCODING, '');
        if (! $this->cookie) {
            $a = realpath(__DIR__ . '/../../../../../web/uploads/cookies');
            $this->cookie = tempnam($a, "nfg");
        }
        curl_setopt($this->ch, CURLOPT_COOKIEFILE, $this->cookie);
        curl_setopt($this->ch, CURLOPT_COOKIEJAR, $this->cookie);
    }

    public function setUsername($var)
    {
        $this->username = $var;
    }

    public function setPassword($var)
    {
        $this->password = $var;
    }

    public function login()
    {
        if ($this->isLoggedIn) {
            return true;
        }
        $this->handShake();
        $header = array(
            'Referer: https://nfg.sefaz.rs.gov.br/Login/LoginNfg.aspx'
        );
        $this->_common($header);
        $data = http_build_query(array(
            'nro_cpf_loginNfg' => $this->username,
            'senha_loginNfg' => $this->password,
            'sistema' => 'NFG',
            'mostraCaptcha' => 'False'
        ));
        curl_setopt($this->ch, CURLOPT_POST, 1);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
        // curl_setopt($this->ch, CURLOPT_NOBODY, 1);
        $url = $this->url . $this->services['login'];
        curl_setopt($this->ch, CURLOPT_URL, $url);
        $result = curl_exec($this->ch);
        // $result = explode("\r\n\r\n", $result);
        $this->isLoggedIn = strpos($result, 'HTTP/1.1 302 Found') !== 0;
        curl_close($this->ch);
        return $result;
    }

    public function profile()
    {
        if (! $this->login()) {
            return false;
        }
        $header = array(
            'Referer: https://nfg.sefaz.rs.gov.br/Site/Inicio.aspx'
        );
        $this->_common($header);
        curl_setopt($this->ch, CURLOPT_HEADER, 0);
        curl_setopt($this->ch, CURLOPT_URL, $this->url . $this->services['profile']);
        $result = curl_exec($this->ch);
        curl_close($this->ch);
        $dom = new \DOMDocument();
        $a = mb_convert_encoding($result, 'HTML-ENTITIES', "UTF-8");
        if (! @$dom->loadHTML($a)) {
            curl_close($this->ch);
            return false;
        }
        $maps = array(
            'cep' => array(
                'id' => 'txtCep',
                'type' => 'input'
            ),
            'telefone' => array(
                'id' => 'txtNroFoneContato',
                'type' => 'input'
            ),
            'email_principal' => array(
                'id' => 'txtEmailPrinc',
                'type' => 'input'
            ),
            'email_opcional' => array(
                'id' => 'txtEmailOpc1',
                'type' => 'input'
            ),
            'frase_seguranca' => array(
                'id' => 'txtTexFraseSeguranca',
                'type' => 'input'
            ),
            'uf' => array(
                'id' => 'uf_resposta',
                'type' => 'label'
            ),
            'municipio' => array(
                'id' => 'municipio_resposta',
                'type' => 'label'
            )            
        );
        $var = array();        
        foreach ($maps as $idx => $map) {
            $node = $dom->getElementById($map['id']);
            if (! $node) {
                return false;
            }
            switch ($map['type']) {
                case 'label':
                    $var[$idx] = $node->nodeValue;
                    break;
                case 'input':
                    $var[$idx] = $node->attributes->getNamedItem('value')->nodeValue;
                    break;
            }
        }
        $xpath = new \DOMXPath($dom);
        $node = $xpath->query('//*[@class="nome"]');
        if ($node->length) {
            $val = $node->item(0)->nodeValue;
            if (preg_match('/(.+)\((.+)\)/', $val, $matchs)) {
                $var['nome'] = trim($matchs[1]);
                $var['cpf'] = trim(preg_replace('/[^0-9]/', '', $matchs[2]));
            }            
        }
        return $var;
    }

    public function register()
    {
        throw new \Exception('na na na');
    }
    
    public function __destruct()
    {
        @unlink($this->cookie);
    }
}
