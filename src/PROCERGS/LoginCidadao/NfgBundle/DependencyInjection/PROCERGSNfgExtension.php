<?php

namespace PROCERGS\LoginCidadao\NfgBundle\DependencyInjection;

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

        $this->registerSoapClient($container, $config);
        $this->registerNfgCredentials($container, $authentication);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    /**
     * @param ContainerBuilder $container
     * @param array $config
     */
    private function registerSoapClient(ContainerBuilder $container, array $config)
    {
        $soapOptions = [];
        if (!$config['verify_https']) {
            $soapOptions['stream_context'] = stream_context_create(
                [
                    'ssl' => [
                        // set some SSL/TLS specific options
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true,
                    ],
                ]
            );
        }
        $soapClient = new Definition('\SoapClient', [$config['endpoints']['wsdl'], $soapOptions]);
        $container->setDefinition('procergs.nfg.soap_client', $soapClient)
            ->setPublic(false);
    }

    /**
     * @param ContainerBuilder $container
     * @param array $authentication
     */
    private function registerNfgCredentials(ContainerBuilder $container, array $authentication)
    {
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
    }
}
