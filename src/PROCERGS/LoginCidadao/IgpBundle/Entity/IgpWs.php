<?php
namespace PROCERGS\LoginCidadao\IgpBundle\Entity;

class IgpWs
{

    protected $url;

    protected $ch;

    protected $cookie;

    protected $username;

    protected $password;

    protected $proxy;

    protected $rg;

    protected $cpf;

    public function setUrl($var)
    {
        $this->url = $var;
    }

    public function setRg($var)
    {
        $this->rg = $var;
    }

    public function setCpf($var)
    {
        $this->cpf = $var;
    }

    public function setProxy($var)
    {
        $this->proxy = $var;
    }

    public function setUsername($var)
    {
        $this->username = $var;
    }

    public function setPassword($var)
    {
        $this->password = $var;
    }

    public function __construct()
    {
        $this->commonHeader = array(
            'Accept: */*',
            'Accept-Language: pt-BR,pt;q=0.8,en-US;q=0.5,en;q=0.3',
            'Connection: keep-alive',
            'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
            'Pragma: no-cache',
            'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:27.0) Gecko/20100101 Firefox/27.0',
            'X-Requested-With: XMLHttpRequest'
        );
    }

    private function _common($header = array())
    {
        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_HEADER, 0);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        if (! ini_get('open_basedir')) {
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
        $headApp = array_merge($this->commonHeader, $header);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headApp);
        if (! $this->cookie) {
            $this->cookie = tempnam($this->sys_get_temp_dir(), "dne");
        }
        curl_setopt($this->ch, CURLOPT_COOKIEFILE, $this->cookie);
        curl_setopt($this->ch, CURLOPT_COOKIEJAR, $this->cookie);
    }

    protected function sys_get_temp_dir()
    {
        $tmp = getenv('TMPDIR');
        if ($tmp && @is_writable($tmp)) {
            $tmpDir = $tmp;
        } elseif (function_exists('sys_get_temp_dir') && @is_writable(sys_get_temp_dir())) {
            $tmpDir = sys_get_temp_dir();
        } else {
            $tmpDir = ini_get('upload_tmp_dir');
        }
        return $tmpDir;
    }

    public function consultar()
    {
        if (!$this->ch) {
            $header = array(
                'organizacao: PROCERGS',
                'matricula: ' . $this->username,
                'senha: ' . $this->password,
                'sistema: IRS',
                'objeto: SIIINDIVIDUOBASICO',
                'acao: CONSULTAR'
            );
            $this->_common($header);
            curl_setopt($this->ch, CURLOPT_POST, 1);
            curl_setopt($this->ch, CURLOPT_URL, $this->url);
        }
        $data = array();
        if ($this->rg) {
            $data['rg'] = $this->rg;
        }
        if ($this->cpf) {
            $data['cpf'] = $this->cpf;
        }
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($data));
        $result = curl_exec($this->ch);
        return json_decode($result, true);
    }

    public function __destruct()
    {
        if (null !== $this->ch) {
            curl_close($this->ch);
            $this->ch = null;
        }
        if (null !== $this->cookie) {
            @unlink($this->cookie);
            $this->cookie = null;
        }
    }
}
