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

        $copySource = 'zip://'.$this->filename.'#';
        for ($i = 0; $i < $this->numFiles; $i++) {
            $entry = $this->getNameIndex($i);
            $filename = basename($entry);

            if ($this->matchFileToFilter($filename, $filters)) {
                $base = dirname($entry);
                $newPath = $directory.DIRECTORY_SEPARATOR.$base.DIRECTORY_SEPARATOR;
                $this->createDir($newPath);

                // extract file
                copy($copySource.$entry, $newPath.$filename);
            }
        }
    }

    protected function createDir($path)
    {
        if (!is_dir($path)) {
            if (!mkdir($path, self::CHMOD, true)) {
                throw new Exception('unable to create path '.$path);
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

            if (!ctype_alnum($filter[0]) && preg_match($filter, $filename)) {
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
    if (!$f) {
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
        echo curl_error($ch);
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
    } elseif (is_string($in)) {
        if (!mb_check_encoding($in, 'UTF-8')
            OR !($in === mb_convert_encoding(mb_convert_encoding($in, 'UTF-32', 'UTF-8'), 'UTF-8', 'UTF-32'))) {
                $in = mb_convert_encoding($in, 'UTF-8');
            }
            return $in;
    } else {
        return $in;
    }
    return $out;
}
