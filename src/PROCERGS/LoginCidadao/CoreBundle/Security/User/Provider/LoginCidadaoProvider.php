<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Security\User\Provider;

use FOS\UserBundle\Security\UserProvider;

class LoginCidadaoProvider extends UserProvider
{
    
    /**
     * Finds a user by username or em-amil or cpf.
     *
     * This method is meant to be an extension point for child classes.
     *
     * @param string $username
     *
     * @return UserInterface|null
     */
    protected function findUser($username)
    {
        return $this->userManager->findUserByUsernameOrEmail($username);
    }
}
