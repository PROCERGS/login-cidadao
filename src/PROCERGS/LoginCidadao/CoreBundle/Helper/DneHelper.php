<?php
namespace PROCERGS\LoginCidadao\CoreBundle\Helper;

class DneHelper
{

    protected $url = 'http://dne.procergs.reders/dne/controller.jsp';

    protected $key = 'h3d8s74gf5';

    protected $ch;

    protected $cookie;

    protected $proxy;

    public function setProxy($var)
    {
        $this->proxy = $var;
    }

    private function _common($header = array())
    {
        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_HEADER, 0);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        if (ini_get('open_basedir')) {
            
        } else {
            curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
        }
        $proxy = $this->proxy;
        if (isset($proxy['type'], $proxy['host'], $proxy['port'])) {
            curl_setopt($this->ch, CURLOPT_PROXYTYPE, $proxy['type']);
            curl_setopt($this->ch, CURLOPT_PROXY, $proxy['host']);
            curl_setopt($this->ch, CURLOPT_PROXYPORT, $proxy['port']);
            if (isset($proxy['auth'])) {
                curl_setopt($this->ch, CURLOPT_PROXYUSERPWD, $proxy['auth']);
            }
        }
        $headApp = array(
            'Accept: */*',
            'Accept-Language: pt-BR,pt;q=0.8,en-US;q=0.5,en;q=0.3',
            'Connection: keep-alive',
            'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
            'Host: dne.procergs.reders',
            'Pragma: no-cache',
            'Referer: http://dne.procergs.reders/dne/',
            'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:27.0) Gecko/20100101 Firefox/27.0',
            'X-Requested-With: XMLHttpRequest'
        );
        $headApp = array_merge($headApp, $header);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headApp);
        if (! $this->cookie) {
            $this->cookie = tempnam(sys_get_temp_dir(), "dne");
        }
        curl_setopt($this->ch, CURLOPT_COOKIEFILE, $this->cookie);
        curl_setopt($this->ch, CURLOPT_COOKIEJAR, $this->cookie);
    }

    public function findByCep($var)
    {
        $this->_common();
        $data = http_build_query(array(
            'action' => 'buscaporcep',
            'key' => $this->key,
            'cep' => $var
        ));
        $url = $this->url . '?' . $data;
        curl_setopt($this->ch, CURLOPT_URL, $url);
        $result = curl_exec($this->ch);
        curl_close($this->ch);
        return $this->_decode($result);
    }

    public function find($var = array())
    {
        $this->_common();
        $data = http_build_query(array(
            'logradouro' => isset($var['logradouro']) ? $var['logradouro'] : '',
            'localidade' => isset($var['localidade']) ? $var['localidade'] : '',
            'uf' => isset($var['uf']) ? $var['uf'] : '',
            'numero' => isset($var['numero']) ? $var['numero'] : ''
        ));
        $dataGet = http_build_query(array(
            'action' => 'pesquisa',
            'key' => $this->key
        ));
        curl_setopt($this->ch, CURLOPT_URL, $this->url . '?' . $dataGet);
        curl_setopt($this->ch, CURLOPT_POST, 1);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($this->ch);
        curl_close($this->ch);
        return $this->_decode($result);
    }
    
    private function _decode(&$result) {
        if ($result === false) {
            return false;
        }
        $dom = new \DOMDocument();
        if (! @$dom->loadXML($result)) {
            return false;
        }
        $node = $dom->getElementsByTagName('valor');
        if (! $node) {
            return false;
        }
        return json_decode($node->item(0)->nodeValue, true);
    } 

}
