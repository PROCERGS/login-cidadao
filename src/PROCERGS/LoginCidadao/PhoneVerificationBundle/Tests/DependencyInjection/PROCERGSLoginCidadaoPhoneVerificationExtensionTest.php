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

use PROCERGS\LoginCidadao\PhoneVerificationBundle\DependencyInjection\PROCERGSLoginCidadaoPhoneVerificationExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class PROCERGSLoginCidadaoPhoneVerificationExtensionTest extends \PHPUnit_Framework_TestCase
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
        $config = [];

        $container = $this->createContainer();
        $container->registerExtension(new PROCERGSLoginCidadaoPhoneVerificationExtension());
        $container->loadFromExtension('procergs_login_cidadao_phone_verification', $config);
        $this->compileContainer($container);
    }
}