<?php

namespace PROCERGS\Generic\HWIOAuthProxyBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use PROCERGS\Generic\HWIOAuthProxyBundle\DependencyInjection\Compiler\ProxyConfigCompilerPass;

class PROCERGSGenericHWIOAuthProxyBundle extends Bundle
{

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ProxyConfigCompilerPass());
    }

    public function getParent()
    {
        return 'HWIOAuthBundle';
    }

}
