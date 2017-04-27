<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\PhoneVerificationBundle\Tests\DependencyInjection;

use LoginCidadao\PhoneVerificationBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public static function getSampleConfig()
    {
        return [
            'enabled' => false,
            'verification_code' => [
                'length' => 6,
                'use_numbers' => true,
                'case_sensitive' => false,
                'use_lower' => false,
                'use_upper' => false,
            ],
            'verification_token' => [
                'length' => 6,
            ],
            'sms' => [
                'resend_timeout' => '+5 minutes',
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
        $config = $processor->processConfiguration(new Configuration(), []);
        $expected = $this->getSampleConfig();
        $this->assertEquals($expected, $config);
    }

    public function testFullConfig()
    {
        $processor = new Processor();
        $expected = $this->getSampleConfig();
        $config = $processor->processConfiguration(new Configuration(), [$expected]);
        $this->assertEquals($expected, $config);
    }
}
