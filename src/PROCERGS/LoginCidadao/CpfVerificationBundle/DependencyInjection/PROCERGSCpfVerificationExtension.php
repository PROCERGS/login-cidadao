<?php

namespace PROCERGS\LoginCidadao\CpfVerificationBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class PROCERGSCpfVerificationExtension extends Extension
{
    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('procergs.nfg.cpf_verification.base_uri', $config['base_uri']);

        $endpoints = [];
        if (isset($config['list_challenges_path'])) {
            $endpoints['listChallenges'] = $config['list_challenges_path'];
        }
        if (isset($config['challenge_path'])) {
            $endpoints['challenge'] = $config['challenge_path'];
        }
        $container->setParameter('procergs.nfg.cpf_verification.options.endpoints', $endpoints);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
