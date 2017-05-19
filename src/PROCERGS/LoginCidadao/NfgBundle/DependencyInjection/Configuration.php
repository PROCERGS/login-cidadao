<?php

namespace PROCERGS\LoginCidadao\NfgBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('procergs_nfg');

        $rootNode
            ->children()
                ->booleanNode('verify_https')
                    ->info('When false, errors such as invalid TLS certificates will be ignored')
                    ->defaultTrue()
                ->end()
                ->arrayNode('circuit_breaker')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('max_failures')
                            ->min(1)
                            ->defaultValue(2)
                        ->end()
                        ->integerNode('reset_timeout')
                            ->min(1)
                            ->defaultValue(30)
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('endpoints')
                    ->cannotBeEmpty()
                    ->isRequired()
                    ->children()
                        ->scalarNode('wsdl')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('login')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('authorization')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('authentication')
                    ->cannotBeEmpty()
                    ->isRequired()
                    ->children()
                        ->scalarNode('organization')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('username')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('password')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('hmac_secret')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
