<?php
namespace PROCERGS\LoginCidadao\CoreBundle\DependencyInjection\Security\Factory;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\FormLoginFactory;

class LoginCidadaoFactory extends FormLoginFactory
{

    public function getPosition()
    {
        return 'pre_auth';
    }
    
    protected function getListenerId()
    {
        return 'procergs_logincidadao.security.authentication.listener';
    }
}
