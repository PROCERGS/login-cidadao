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

        $args['oauth2.server.grant_types'] = array();

        $args['oauth2.server.response_types'] = array(
            'token' => new Reference('oauth2.response_types.token'),
            'code' => new Reference('oauth2.response_types.code'),
            'id_token' => new Reference('oauth2.response_types.id_token'),
            'id_token token' => new Reference('oauth2.response_types.id_token_token'),
            'code id_token' => new Reference('oauth2.response_types.code_id_token'),
        );
        $definition->setArguments($args);

        if ($container->hasDefinition('gaufrette.jwks_fs_filesystem')) {
            $filesystem = new Reference('gaufrette.jwks_fs_filesystem');
            $fileName   = $container->getParameter('jwks_private_key_file');
            $container->getDefinition('oauth2.storage.public_key')
                ->addMethodCall('setFilesystem', array($filesystem, $fileName));
        }

        if ($container->hasDefinition('oauth2.grant_type.authorization_code')) {
            $sessionState = new Reference('oidc.storage.session_state');
            $container->getDefinition('oauth2.grant_type.authorization_code')
                ->addMethodCall('setSessionStateStorage', array($sessionState));
        }
        if ($container->hasDefinition('oauth2.storage.authorization_code')) {
            $sessionState = new Reference('oidc.storage.session_state');
            $container->getDefinition('oauth2.storage.authorization_code')
                ->addMethodCall('setSessionStateStorage', array($sessionState));
        }

        if ($container->hasDefinition('oauth2.scope_manager')) {
            $scopes = $container->getParameter('lc_supported_scopes');
            $container->getDefinition('oauth2.scope_manager')
                ->addMethodCall('setScopes', array($scopes));
        }

        if ($container->hasDefinition('oauth2.storage.access_token')) {
            $secret = $container->getParameter('secret');
            $container->getDefinition('oauth2.storage.access_token')
                ->addMethodCall('setPairwiseSubjectIdSalt', array($secret));
        }
    }
}
