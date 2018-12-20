<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Tests\Diagnostics;

use LoginCidadao\CoreBundle\Diagnostics\RedisServiceCheck;
use PHPUnit\Framework\TestCase;
use Predis\ClientException;
use ZendDiagnostics\Result\Failure;
use ZendDiagnostics\Result\Success;
use ZendDiagnostics\Result\Warning;

/**
 * @group time-sensitive
 */
class RedisServiceCheckTest extends TestCase
{
    public function testNoService()
    {
        $result = (new RedisServiceCheck(null))->check();

        $this->assertInstanceOf(Warning::class, $result);
    }

    public function testSuccess()
    {
        $key = null;
        $value = null;
        $redis = $this->createMock(RedisClientMockInterface::class);

        $redis->expects($this->once())->method('set')
            ->willReturnCallback(function ($usedKey, $usedValue) use (&$key, &$value) {
                $key = $usedKey;
                $value = $usedValue;
            });

        $redis->expects($this->once())->method('get')
            ->willReturnCallback(function ($getKey) use (&$key, &$value) {
                $this->assertSame($key, $getKey);

                return $value;
            });

        $redis->expects($this->once())->method('del')->with($this->isType('array'))
            ->willReturnCallback(function ($delKey) use (&$key, &$value) {
                $this->assertSame($key, $delKey[0]);

                return true;
            });

        $result = (new RedisServiceCheck($redis))->check();
        $this->assertInstanceOf(Success::class, $result);
    }

    public function testException()
    {
        $redis = $this->createMock(RedisClientMockInterface::class);

        $redis->expects($this->once())->method('set')->willThrowException(new ClientException());
        $result = (new RedisServiceCheck($redis))->check();

        $this->assertInstanceOf(Failure::class, $result);
    }

    public function testUnexpectedResponse()
    {
        $redis = $this->createMock(RedisClientMockInterface::class);

        $redis->expects($this->once())->method('set');
        $redis->expects($this->once())->method('get')->willReturn('surprise!');
        $result = (new RedisServiceCheck($redis))->check();

        $this->assertInstanceOf(Failure::class, $result);
    }
}
