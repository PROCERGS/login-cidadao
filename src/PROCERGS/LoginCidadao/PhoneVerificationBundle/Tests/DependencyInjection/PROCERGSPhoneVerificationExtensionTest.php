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

use PROCERGS\LoginCidadao\PhoneVerificationBundle\DependencyInjection\PROCERGSPhoneVerificationExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class PROCERGSPhoneVerificationExtensionTest extends \PHPUnit_Framework_TestCase
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
        $config = ConfigurationTest::getConfig(5, 20);

        $container = $this->createContainer();
        $container->registerExtension(new PROCERGSPhoneVerificationExtension());
        $container->loadFromExtension('procergs_phone_verification', $config);
        $this->compileContainer($container);

        $this->assertEquals(
            $config['max_failures'],
            $container->getParameter('procergs_phone_verification.max_failures')
        );
        $this->assertEquals(
            $config['reset_timeout'],
            $container->getParameter('procergs_phone_verification.reset_timeout')
        );
    }
}
