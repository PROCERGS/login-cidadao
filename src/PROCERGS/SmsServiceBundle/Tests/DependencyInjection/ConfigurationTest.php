<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Created by PhpStorm.
 * User: gdnt
 * Date: 04/04/17
 * Time: 11:41
 */

namespace PROCERGS\SmsServiceBundle\Tests\DependencyInjection;


use PROCERGS\SmsServiceBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public static function getConfig()
    {
        return [
            'uri' => [
                'send' => 'https://some.address/send',
                'receive' => 'https://some.address/send',
                'status' => 'https://some.address/send',
            ],
            'system' => [
                'realm' => 'my_realm',
                'id' => 'SYSTEM',
                'key' => 'SECRET_KEY',
            ],
            'send' => true,
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
        $this->setExpectedException('Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');
        $processor = new Processor();
        $processor->processConfiguration(new Configuration(), []);
    }

    public function testFullConfig()
    {
        $processor = new Processor();
        $expected = $this->getConfig();
        $config = $processor->processConfiguration(new Configuration(), [$expected]);
        $this->assertEquals($expected, $config);
    }
}
