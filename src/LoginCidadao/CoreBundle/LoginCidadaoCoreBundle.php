<?php

namespace LoginCidadao\CoreBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use LoginCidadao\CoreBundle\DependencyInjection\Security\Factory\LoginCidadaoFactory;

class LoginCidadaoCoreBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
    
        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new LoginCidadaoFactory());
    }    
}
