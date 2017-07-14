<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\PhoneVerificationBundle\Tests\DependencyInjection;

use PROCERGS\LoginCidadao\PhoneVerificationBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public static function getConfig($maxFailures = 2, $resetTimeout = 10)
    {
        return [
            'max_failures' => $maxFailures ?: 2,
            'reset_timeout' => $resetTimeout ?: 10,
        ];
    }

    public function testGetConfigTreeBuilder()
    {
        $configuration = new Configuration();
        $this->assertInstanceOf(
            'Symfony\Component\Config\Definition\Builder\TreeBuilder',
            $configuration->getConfigTreeBuilder()
        );
    }

    public function testEmptyConfig()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), []);
        $this->assertEquals(self::getConfig(), $config);
    }

    public function testMaxFailures()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [['max_failures' => 5]]);
        $this->assertEquals(self::getConfig(5), $config);
    }

    public function testResetTimeout()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [['reset_timeout' => 20]]);
        $this->assertEquals(self::getConfig(null, 20), $config);
    }
}
