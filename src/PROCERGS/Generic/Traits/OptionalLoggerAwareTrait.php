<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\Generic\Traits;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

trait OptionalLoggerAwareTrait
{
    use LoggerAwareTrait;

    /**
     * Logs with an arbitrary level if logger is available.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    protected function log($level, $message, array $context = array())
    {
        if ($this->logger instanceof LoggerInterface) {
            $this->logger->log($level, $message, $context);
        }
    }
}
