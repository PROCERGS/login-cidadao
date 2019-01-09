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
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    public static function getSampleConfig()
    {
        return [
            'enabled' => false,
            'require_validation_threshold' => 3,
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
            'blocklist' => [
                'enable_auto_block' => true,
                'auto_block_limit' => 10,
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
