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
use PROCERGS\LoginCidadao\CpfVerificationBundle\DependencyInjection\PROCERGSCpfVerificationExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class PROCERGSCpfVerificationExtensionTest extends TestCase
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
        $container = $this->createContainer();
        $container->registerExtension(new PROCERGSCpfVerificationExtension());
        $container->loadFromExtension('procergs_cpf_verification', [
            'base_uri' => 'https://example.com',
            'list_challenges_path' => 'challenges',
            'challenge_path' => 'challenges/challenge',
        ]);
        $this->compileContainer($container);

        $endpoints = [
            'listChallenges' => 'challenges',
            'challenge' => 'challenges/challenge',
        ];

        $this->assertEquals(
            'https://example.com',
            $container->getParameter('procergs.nfg.cpf_verification.base_uri')
        );
        $this->assertEquals(
            $endpoints,
            $container->getParameter('procergs.nfg.cpf_verification.options.endpoints')
        );
    }
}
