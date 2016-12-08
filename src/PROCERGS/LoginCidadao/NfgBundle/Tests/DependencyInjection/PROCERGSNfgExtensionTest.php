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

use PROCERGS\LoginCidadao\NfgBundle\DependencyInjection\PROCERGSNfgExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class PROCERGSNfgExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return ContainerBuilder
     */
    private function createContainer()
    {
        $container = new ContainerBuilder(
            new ParameterBag(
                array(
                    'kernel.cache_dir' => __DIR__,
                    'kernel.root_dir' => __DIR__.'/Fixtures',
                    'kernel.charset' => 'UTF-8',
                    'kernel.debug' => false,
                    'kernel.bundles' => array('PROCERGSLoginCidadaoMonitorBundle' => 'PROCERGS\\LoginCidadao\\MonitorBundle\\PROCERGSLoginCidadaoMonitorBundle'),
                )
            )
        );

        return $container;
    }

    private function compileContainer(ContainerBuilder $container)
    {
        $container->getCompilerPassConfig()->setOptimizationPasses(array());
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->compile();
    }

    public function testParametersLoaded()
    {
        $config = ConfigurationTest::getSampleConfig();
        $config['circuit_breaker']['service_name'] = 'cb_service_name';
        $container = $this->createContainer();
        $container->registerExtension(new PROCERGSNfgExtension());
        $container->loadFromExtension('procergs_nfg', $config);
        $this->compileContainer($container);

        $this->assertEquals('cb_service_name', $container->getParameter('procergs.nfg.circuit_breaker.service_name'));

        $endpoints = $config['endpoints'];
        $endpointsPrefix = 'procergs.nfg.endpoints.';
        $this->assertEquals($endpoints['wsdl'], $container->getParameter($endpointsPrefix.'wsdl'));
        $this->assertEquals($endpoints['login'], $container->getParameter($endpointsPrefix.'login'));
        $this->assertEquals($endpoints['authorization'], $container->getParameter($endpointsPrefix.'authorization'));

        $authN = $config['authentication'];
        $authNPrefix = 'procergs.nfg.authentication.';
        $this->assertEquals($authN['organization'], $container->getParameter($authNPrefix.'organization'));
        $this->assertEquals($authN['username'], $container->getParameter($authNPrefix.'username'));
        $this->assertEquals($authN['password'], $container->getParameter($authNPrefix.'password'));
        $this->assertEquals($authN['hmac_secret'], $container->getParameter($authNPrefix.'hmac_secret'));
    }
}
