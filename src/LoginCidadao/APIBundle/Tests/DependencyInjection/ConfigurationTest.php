<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\APIBundle\Tests\DependencyInjection;

use LoginCidadao\APIBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public static function getSampleConfig()
    {
        return [
            'versions' => [
                '1' => [
                    '0' => [0, 1, 2],
                ],
                '2' => [
                    '0' => [0, 2],
                    '1' => [0, 1, 2, 3],
                ],
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
        $this->setExpectedException('Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');
        $processor = new Processor();
        $processor->processConfiguration(new Configuration(), []);
    }

    public function testIncompleteConfig()
    {
        $this->setExpectedException('Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');
        $processor = new Processor();
        $processor->processConfiguration(new Configuration(), [
            [
                'versions' => [
                    '1' => [],
                ],
            ],
        ]);
    }

    public function testSampleConfig()
    {
        $processor = new Processor();
        $processor->processConfiguration(new Configuration(), [static::getSampleConfig()]);
    }
}
