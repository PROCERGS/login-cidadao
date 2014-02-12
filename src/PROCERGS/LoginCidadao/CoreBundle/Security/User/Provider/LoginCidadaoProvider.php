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
        if (preg_match('/^([0-9]{3})\.([0-9]{3})\.([0-9]{3})-([0-9]{2})$/', $username, $regs)) {
            array_shift($regs);
            $person = $this->userManager->findUserBy(array('cpf' => implode('', $regs)));
            if ($person !== null) {
                return $person;
            }
        } 
        return $this->userManager->findUserByUsernameOrEmail($username);
    }
}
