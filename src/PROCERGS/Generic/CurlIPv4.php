<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\Generic;

use Buzz\Client\Curl;

/**
 * Class to force the Buzz's Curl client to use only IPv4.
 *
 * This is needed because we get random errors in some environments
 * where cURL tries to make IPv6 requests even when IPv6 is disabled
 * in the Operating System.
 *
 * @package PROCERGS\Generic
 */
class CurlIPv4 extends Curl
{
    public function __construct()
    {
        parent::__construct();

        // If the fix is available we go for it, otherwise there is nothing we can do.
        if (defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')) {
            $this->setOption(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        }
    }
}
