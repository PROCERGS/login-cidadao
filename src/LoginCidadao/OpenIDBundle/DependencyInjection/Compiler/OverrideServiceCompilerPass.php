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
        $issuer = $container->getParameter('jwt_iss');

        $definition = $container->getDefinition('oauth2.server');
        $args       = $definition->getArguments();

        $args['oauth2.server.storage'][] = new Reference('oauth2.storage.user_claims');
        $args['oauth2.server.storage'][] = new Reference('oauth2.storage.public_key');
        $definition->setArguments($args);

        if ($container->hasDefinition('gaufrette.jwks_fs_filesystem')) {
            $filesystem = new Reference('gaufrette.jwks_fs_filesystem');
            $fileName   = $container->getParameter('jwks_private_key_file');
            $container->getDefinition('oauth2.storage.public_key')
                ->addMethodCall('setFilesystem', array($filesystem, $fileName));
        }

        if ($container->hasDefinition('oauth2.storage.access_token')) {
            $dispatcher = new Reference('event_dispatcher');
            $container->getDefinition('oauth2.storage.access_token')
                ->addMethodCall('setEventDispatcher', array($dispatcher));
        }
    }
}
