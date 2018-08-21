<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\CpfVerificationBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use PROCERGS\LoginCidadao\CpfVerificationBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    private const FULL_CONFIG = [
        'base_uri' => 'https://example.com',
        'list_challenges_path' => 'challenges/list',
        'challenge_path' => 'challenges/get/:challenge',
    ];

    public function testGetConfigTreeBuilder()
    {
        $configuration = new Configuration();
        $this->assertInstanceOf(TreeBuilder::class, $configuration->getConfigTreeBuilder());
    }

    public function testEmptyConfig()
    {
        $this->expectException(\Exception::class);

        $processor = new Processor();
        $processor->processConfiguration(new Configuration(), []);
    }

    public function testMinimalConfig()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [['base_uri' => 'https://example.com']]);

        $this->assertEquals([
            'base_uri' => 'https://example.com',
            'list_challenges_path' => null,
            'challenge_path' => null,
        ], $config);
    }

    public function testFullConfig()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [self::FULL_CONFIG]);

        $this->assertEquals(self::FULL_CONFIG, $config);
    }
}
