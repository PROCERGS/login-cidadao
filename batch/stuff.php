<?php
class Zip_Manager extends \ZipArchive
{

    const CHMOD = 0755;

    public function filteredExtractTo($directory, array $filters = null)
    {
        if (count($filters) === 0) {
            return $this->extractTo($directory);
        }

        $this->createDir($directory);

        $copySource = 'zip://' . $this->filename . '#';
        for ($i = 0; $i < $this->numFiles; $i ++) {
            $entry = $this->getNameIndex($i);
            $filename = basename($entry);

            if ($this->matchFileToFilter($filename, $filters)) {
                $base = dirname($entry);
                $newPath = $directory . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR;
                $this->createDir($newPath);

                // extract file
                copy($copySource . $entry, $newPath . $filename);
            }
        }
    }

    protected function createDir($path)
    {
        if (! is_dir($path)) {
            if (! mkdir($path, self::CHMOD, true)) {
                throw new Exception('unable to create path ' . $path);
            }
        }
    }

    protected function matchFileToFilter($filename, array $filters)
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($ext, array_map('strtolower', $filters))) {

            return true;
        }

        foreach ($filters as $i => $filter) {

            if (! ctype_alnum($filter[0]) && preg_match($filter, $filename)) {
                return true;
            }
        }
        return false;
    }
}

function getTempNam($prefix = "php")
{
    $tmp = getenv('TMPDIR');
    if ($tmp && @is_writable($tmp)) {
        $tmpDir = $tmp;
    } elseif (function_exists('sys_get_temp_dir') && @is_writable(sys_get_temp_dir())) {
        $tmpDir = sys_get_temp_dir();
    }
    return tempnam($tmpDir, $prefix);
}

function file_get_contents_curl($url, $file, $referer = null)
{
    $ch = curl_init();
    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $f = fopen($file, 'wb');
    if (! $f) {
        return false;
    }
    curl_setopt($ch, CURLOPT_FILE, $f);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_ENCODING, '');
    curl_setopt($ch, CURLOPT_URL, $url);
    if ($referer) {
        curl_setopt($ch, CURLOPT_REFERER, $referer);
    }
    $result = curl_exec($ch);
    if (!$result) {
        echo curl_error ($ch );
    }
    curl_close($ch);
    fclose($f);
    return $result;
}

function getPDOConnection($config)
{
    $db_driver = $config['database_driver'];
    $db_host = $config['database_host'];
    $db_name = $config['database_name'];
    $db_user = $config['database_user'];
    $db_pass = $config['database_password'];
    $db_port = $config['database_port'];
    $pdo = new PDO("$db_driver:host=$db_host;port=$db_port;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->query("SET NAMES 'UTF8'");
    return $pdo;
}
function trim2($s)
{
    $s = trim($s, " \t\n\r\0\x0B-");
    if ('' === $s) {
        return null;
    } else {
        return $s;
    }
}
function utf8_encode_recursivo($in)
{
    if (is_array($in)) {
        foreach ($in as $key => $value) {
            $out[utf8_encode_recursivo($key)] = utf8_encode_recursivo($value);
        }
    } elseif(is_string($in)) {
        if(!mb_check_encoding($in, 'UTF-8')
            OR !($in === mb_convert_encoding(mb_convert_encoding($in, 'UTF-32', 'UTF-8' ), 'UTF-8', 'UTF-32'))) {
                $in = mb_convert_encoding($in, 'UTF-8');
            }
            return $in;
    } else {
        return $in;
    }
    return $out;
}
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
            $tmp = getenv('TMPDIR');
            if ($tmp && @is_writable($tmp)) {
                $tmpDir = $tmp;
            } elseif (function_exists('sys_get_temp_dir') && @is_writable(sys_get_temp_dir())) {
                $tmpDir = sys_get_temp_dir();
            }
            $this->cookie = tempnam($tmpDir, "dne");
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
            'state' => isset($var['state']) ? $var['state'] : '',
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