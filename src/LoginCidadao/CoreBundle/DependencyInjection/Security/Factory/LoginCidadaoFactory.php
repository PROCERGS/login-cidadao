<?php
namespace LoginCidadao\CoreBundle\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\FormLoginFactory;

class LoginCidadaoFactory extends FormLoginFactory
{

    public function getPosition()
    {
        return 'pre_auth';
    }
    
    protected function getListenerId()
    {
        return 'lc.security.authentication.listener';
    }
}
