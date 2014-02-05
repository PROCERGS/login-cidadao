<?php

namespace PROCERGS\Generic\HWIOAuthProxyBundle\Buzz\Client;

use Buzz\Client\Curl;

class CurlProxy extends Curl
{

    public function __construct($proxy = null)
    {
        parent::__construct();
        if (!is_null($proxy)) {
            $this->setProxy($proxy);
        }
    }

}
