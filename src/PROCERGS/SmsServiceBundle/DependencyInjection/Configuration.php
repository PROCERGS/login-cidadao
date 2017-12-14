<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\SmsServiceBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('procergs_sms_service');

        $rootNode
            ->children()
                ->arrayNode('uri')
                    ->children()
                        ->scalarNode('send')
                            ->isRequired()
                        ->end()
                        ->scalarNode('receive')
                            ->isRequired()
                        ->end()
                        ->scalarNode('status')
                            ->isRequired()
                        ->end()
                    ->end()
                    ->isRequired()
                ->end()
                ->arrayNode('system')
                    ->children()
                        ->scalarNode('realm')
                            ->isRequired()
                        ->end()
                        ->scalarNode('id')
                            ->isRequired()
                        ->end()
                        ->scalarNode('key')
                            ->isRequired()
                        ->end()
                    ->end()
                    ->isRequired()
                ->end()
                ->booleanNode('send')
                    ->defaultTrue()
                ->end()
            ->end()
         ;

        return $treeBuilder;
    }
}
