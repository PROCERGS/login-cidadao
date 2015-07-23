<?php
/*
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\Generic\LongPolling;

use PROCERGS\LoginCidadao\APIBundle\Exception\RequestTimeoutException;

class LongPollingUtils
{

    public static function runTimeLimited($callback, $waitTime = 1)
    {
        $maxExecutionTime = ini_get('max_execution_time');
        $limit            = $maxExecutionTime ? $maxExecutionTime - 2 : 60;
        $startTime        = time();
        while ($limit > 0) {
            $result = call_user_func($callback);
            $delta  = time() - $startTime;

            if ($result !== false) {
                return $result;
            }

            $limit -= $delta;
            if ($limit <= 0) {
                break;
            }
            $startTime = time();
            sleep($waitTime);
        }
        throw new RequestTimeoutException("Request Timeout");
    }
}
