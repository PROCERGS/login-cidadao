<?php
/*
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class OverrideServiceCompilerPass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('oauth2.server');
        $args       = $definition->getArguments();

        $args['storage'][] = new Reference('oauth2.storage.user_claims');
        $args['storage'][] = new Reference('oauth2.storage.public_key');
        $args['config']    = array(
            'use_openid_connect' => true,
            'issuer' => $container->getParameter('site_domain'),
            'allow_implicit' => true
        );
        //var_dump($args); die();
        $definition->setArguments($args);
    }
}
