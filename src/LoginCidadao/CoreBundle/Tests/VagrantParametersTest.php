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

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Yaml\Yaml;

class VagrantParametersTest extends KernelTestCase
{

    private $dist;
    private $vagrant;

    protected function setUp()
    {
        self::bootKernel();

        $kernel = self::$kernel;

        $distPath = implode(DIRECTORY_SEPARATOR, [$kernel->getRootDir(), 'config', 'parameters.yml.dist']);
        $vagrantPath = implode(DIRECTORY_SEPARATOR, [$kernel->getRootDir(), 'config', 'parameters.yml.vagrant']);

        $this->dist = Yaml::parse(file_get_contents($distPath));
        $this->vagrant = Yaml::parse(file_get_contents($vagrantPath));
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
