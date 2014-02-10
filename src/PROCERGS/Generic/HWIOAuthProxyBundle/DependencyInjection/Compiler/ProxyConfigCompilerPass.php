<?php

namespace PROCERGS\Generic\HWIOAuthProxyBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;

class ProxyConfigCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        try {
            $proxy = $container->getParameter('http_proxy');

            $definition = $container->getDefinition('buzz.client');
            $definition->setArguments(compact('proxy'));
        } catch (ParameterNotFoundException $e) {

        }
    }
}