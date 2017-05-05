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

use LoginCidadao\PhoneVerificationBundle\DependencyInjection\LoginCidadaoPhoneVerificationExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class LoginCidadaoPhoneVerificationExtensionTest extends \PHPUnit_Framework_TestCase
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
        $config = ConfigurationTest::getSampleConfig();

        $container = $this->createContainer();
        $container->registerExtension(new LoginCidadaoPhoneVerificationExtension());
        $container->loadFromExtension('login_cidadao_phone_verification', $config);
        $this->compileContainer($container);

        $this->assertEquals(
            $config['enabled'],
            $container->getParameter('lc.phone_verification.options.enabled')
        );
        $this->assertEquals(
            $config['verification_code']['length'],
            $container->getParameter('lc.phone_verification.options.code.length')
        );
        $this->assertEquals(
            $config['verification_code']['use_numbers'],
            $container->getParameter('lc.phone_verification.options.code.use_numbers')
        );
        $this->assertEquals(
            $config['verification_code']['case_sensitive'],
            $container->getParameter('lc.phone_verification.options.code.case_sensitive')
        );
        $this->assertEquals(
            $config['verification_code']['use_upper'],
            $container->getParameter('lc.phone_verification.options.code.use_upper')
        );
        $this->assertEquals(
            $config['verification_code']['use_lower'],
            $container->getParameter('lc.phone_verification.options.code.use_lower')
        );
        $this->assertEquals(
            $config['sms']['resend_timeout'],
            $container->getParameter('lc.phone_verification.options.sms.resend_timeout')
        );
        $this->assertEquals(
            $config['verification_token']['length'],
            $container->getParameter('lc.phone_verification.options.token.length')
        );
    }
}
