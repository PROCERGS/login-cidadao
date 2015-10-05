<?php

namespace LoginCidadao\OpenIDBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use LoginCidadao\OpenIDBundle\DependencyInjection\Compiler\OverrideServiceCompilerPass;

class LoginCidadaoOpenIDBundle extends Bundle
{

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new OverrideServiceCompilerPass());
    }
}
