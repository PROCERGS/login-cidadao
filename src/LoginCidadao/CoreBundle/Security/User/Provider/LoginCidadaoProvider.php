<?php

namespace LoginCidadao\CoreBundle\Security\User\Provider;

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
        
        if (is_numeric($cpf = preg_replace('/[^0-9]/', '', $username)) && strlen($cpf) == 11) {
            $person = $this->userManager->findUserBy(array('cpf' => $cpf));
            if ($person !== null) {
                return $person;
            }
        } 
        return $this->userManager->findUserByUsernameOrEmail($username);
    }
}
