<?php

namespace PROCERGS\Generic\HWIOAuthProxyBundle\Buzz\Client;

use Buzz\Client\Curl;
use Buzz\Message\MessageInterface;
use Buzz\Message\RequestInterface;
use Buzz\Exception\ClientException;
use Buzz\Exception\LogicException;


class CurlProxy extends Curl
{

    public function __construct($proxy = null)
    {
        parent::__construct();
        if (!is_null($proxy)) {
            if (is_array($proxy)) {
                $type = $proxy['type'];
                $host = $proxy['host'];
                $port = $proxy['port'];
                $auth = $proxy['auth'];
                $proxy = "$type://$auth@$host:$port";
            }
            $this->setProxy($proxy);
        }
    }
    
    protected static function populateResponse($curl, $raw, MessageInterface $response)
    {
        $pos = explode("\r\n\r\n", $raw);
        
        $response->setContent(array_pop($pos));
        $response->setHeaders(static::getLastHeaders(array_pop($pos)));
    }

    protected static function getLastHeaders($raw)
    {
        $headers = array();
        foreach (preg_split('/(\\r?\\n)/', $raw) as $header) {
            if ($header) {
                $headers[] = $header;
            } else {
                $headers = array();
            }
        }
    
        return $headers;
    }    

}
