<?php

namespace LoginCidadao\PhoneVerificationBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('login_cidadao_phone_verification');

        $rootNode
            ->children()
                ->booleanNode('enabled')
                    ->defaultFalse()
                ->end()
                ->arrayNode('verification_code')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('length')
                            ->defaultValue(6)
                        ->end()
                        ->booleanNode('use_numbers')
                            ->defaultTrue()
                        ->end()
                        ->booleanNode('case_sensitive')
                            ->defaultFalse()
                        ->end()
                        ->booleanNode('use_lower')
                            ->defaultFalse()
                        ->end()
                        ->booleanNode('use_upper')
                            ->defaultFalse()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('verification_token')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('length')
                            ->defaultValue(6)
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('sms')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('resend_timeout')
                            ->defaultValue('+5 minutes')
                        ->end()
                    ->end()
                ->end()
            ->end();

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}
