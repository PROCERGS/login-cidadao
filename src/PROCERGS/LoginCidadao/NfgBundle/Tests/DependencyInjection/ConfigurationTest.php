<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\NfgBundle\Tests\DependencyInjection;

use PROCERGS\LoginCidadao\NfgBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

/**
 * @codeCoverageIgnore
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public static function getSampleConfig()
    {
        return [
            'verify_https' => true,
            'circuit_breaker' => [
                'max_failures' => 2,
                'reset_timeout' => 30,
            ],
            'endpoints' => [
                'wsdl' => 'https://dum.my/service.wsdl',
                'login' => 'https://dum.my/login',
                'authorization' => 'https://dum.my/authorization',
            ],
            'authentication' => [
                'organization' => 'foobar',
                'username' => 'myUser',
                'password' => 'mySuperSecretPassword',
                'hmac_secret' => 'my very secret HMAC string',
            ],
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

        try {
            $processor->processConfiguration(new Configuration(), []);
            $this->fail('The configuration is not failing when receiving invalid data.');
        } catch (\Exception $e) {
            $this->assertInstanceOf('Symfony\Component\Config\Definition\Exception\InvalidConfigurationException', $e);
        }
    }

    public function testFullConfig()
    {
        $processor = new Processor();

        $expected = $this->getSampleConfig();
        $config = $processor->processConfiguration(new Configuration(), [$expected]);

        $this->assertEquals($expected, $config);
    }
}
