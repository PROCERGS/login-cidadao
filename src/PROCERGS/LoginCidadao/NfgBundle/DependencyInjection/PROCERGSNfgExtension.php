<?php

namespace PROCERGS\LoginCidadao\NfgBundle\DependencyInjection;

use PROCERGS\LoginCidadao\NfgBundle\Security\Credentials;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class PROCERGSNfgExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $authentication = [];
        foreach ($config as $type => $values) {
            if (false === array_search($type, ['endpoints', 'authentication'])) {
                continue;
            }
            $prefix = "procergs.nfg.$type.";
            foreach ($values as $key => $value) {
                $container->setParameter($prefix.$key, $value);
                if ($type === 'authentication') {
                    $authentication[$key] = $value;
                }
            }
        }
        $container->setParameter('procergs.nfg.verify_https', $config['verify_https']);

        $credentials = new Definition(
            'PROCERGS\LoginCidadao\NfgBundle\Security\Credentials',
            [
                $authentication['organization'],
                $authentication['username'],
                $authentication['password'],
            ]
        );
        $container->setDefinition('procergs.nfg.credentials', $credentials)
            ->setPublic(false);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
