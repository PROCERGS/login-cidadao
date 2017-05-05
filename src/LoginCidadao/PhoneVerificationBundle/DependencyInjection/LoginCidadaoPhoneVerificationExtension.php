<?php

namespace LoginCidadao\PhoneVerificationBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class LoginCidadaoPhoneVerificationExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter("lc.phone_verification.options.enabled", $config['enabled']);

        foreach ($config['verification_code'] as $key => $value) {
            $container->setParameter("lc.phone_verification.options.code.{$key}", $value);
        }
        foreach ($config['verification_token'] as $key => $value) {
            $container->setParameter("lc.phone_verification.options.token.{$key}", $value);
        }
        foreach ($config['sms'] as $key => $value) {
            $container->setParameter("lc.phone_verification.options.sms.{$key}", $value);
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
