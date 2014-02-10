<?php

namespace PROCERGS\Generic\HWIOAuthProxyBundle\Buzz\Client;

use Buzz\Client\Curl;

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

}
