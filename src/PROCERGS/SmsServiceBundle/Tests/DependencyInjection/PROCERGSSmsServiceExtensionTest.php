<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\SmsServiceBundle\Tests\DependencyInjection;

use PROCERGS\SmsServiceBundle\DependencyInjection\PROCERGSSmsServiceExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class PROCERGSSmsServiceExtensionTest extends \PHPUnit_Framework_TestCase
{
    private function createContainer()
    {
        $container = new ContainerBuilder(
            new ParameterBag(
                [
                    'kernel.cache_dir' => __DIR__,
                    'kernel.root_dir' => __DIR__.'/Fixtures',
                    'kernel.charset' => 'UTF-8',
                    'kernel.debug' => false,
                ]
            )
        );

        return $container;
    }

    private function compileContainer(ContainerBuilder $container)
    {
        $container->getCompilerPassConfig()->setOptimizationPasses([]);
        $container->getCompilerPassConfig()->setRemovingPasses([]);
        $container->compile();
    }

    public function testParametersLoaded()
    {
        $config = ConfigurationTest::getConfig();

        $container = $this->createContainer();
        $container->registerExtension(new PROCERGSSmsServiceExtension());
        $container->loadFromExtension('procergs_sms_service', $config);
        $this->compileContainer($container);

        $this->assertEquals($config['uri']['send'], $container->getParameter('procergs.sms.uri.send'));
        $this->assertEquals($config['uri']['receive'], $container->getParameter('procergs.sms.uri.receive'));
        $this->assertEquals($config['uri']['status'], $container->getParameter('procergs.sms.uri.status'));

        $this->assertEquals($config['system']['realm'], $container->getParameter('procergs.sms.system.realm'));
        $this->assertEquals($config['system']['id'], $container->getParameter('procergs.sms.system.id'));
        $this->assertEquals($config['system']['key'], $container->getParameter('procergs.sms.system.key'));

        $this->assertEquals($config['send'], $container->getParameter('procergs.sms.should_send'));
    }
}
