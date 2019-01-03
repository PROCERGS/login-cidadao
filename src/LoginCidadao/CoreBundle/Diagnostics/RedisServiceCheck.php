<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Diagnostics;

use Predis\ClientInterface;
use Predis\PredisException;
use ZendDiagnostics\Check\CheckInterface;
use ZendDiagnostics\Result\Failure;
use ZendDiagnostics\Result\Success;
use ZendDiagnostics\Result\Warning;

class RedisServiceCheck implements CheckInterface
{
    private const KEY_PREFIX = 'redis_check_';

    /**
     * @var ClientInterface
     */
    private $redis;

    /**
     * RedisServiceCheck constructor.
     * @param ClientInterface $redis
     */
    public function __construct(ClientInterface $redis = null)
    {
        $this->redis = $redis;
    }

    /**
     * @inheritDoc
     */
    public function check()
    {
        if ($this->redis === null) {
            return new Warning('Redis is not configured. Nothing to test...');
        }

        $key = self::KEY_PREFIX.random_int(0, PHP_INT_MAX);
        $value = random_bytes(random_int(10, 255));

        try {
            $this->redis->set($key, $value);
            sleep(2);
            $response = $this->redis->get($key);
            $this->redis->del([$key]);

            if ($response === $value) {
                return new Success("Redis is working. Tested SET, GET and DEL");
            }

            return new Failure("Redis is not working properly. GET didn't return expected value.");
        } catch (PredisException $e) {
            return new Failure("Redis is not working properly. Exception: {$e->getMessage()}");
        }
    }

    /**
     * @inheritDoc
     */
    public function getLabel()
    {
        return 'Predis Redis';
    }
}
