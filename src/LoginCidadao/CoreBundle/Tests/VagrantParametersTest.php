<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * Class VagrantParametersTest
 * @package LoginCidadao\CoreBundle\Tests
 */
class VagrantParametersTest extends TestCase
{
    private $dist;
    private $vagrant;

    protected function setUp()
    {
        $rootDir = realpath(__DIR__.'/../../../../app');
        $distPath = implode(DIRECTORY_SEPARATOR, [$rootDir, 'config', 'parameters.yml.dist']);
        $vagrantPath = implode(DIRECTORY_SEPARATOR, [$rootDir, 'config', 'parameters.yml.vagrant']);

        if (file_exists($distPath) && file_exists($vagrantPath)) {
            $this->dist = Yaml::parse(file_get_contents($distPath));
            $this->vagrant = Yaml::parse(file_get_contents($vagrantPath));
        } else {
            $this->fail("Config files nor found!");
        }
    }

    public function testMissingParameters()
    {
        foreach ($this->dist['parameters'] as $param => $value) {
            $this->assertArrayHasKey($param, $this->vagrant['parameters']);
        }
    }

    public function testUnecessaryParameters()
    {
        foreach ($this->vagrant['parameters'] as $param => $value) {
            $this->assertArrayHasKey($param, $this->dist['parameters']);
        }
    }
}
