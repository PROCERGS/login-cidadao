<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\NfgBundle\Security;

class Credentials
{
    /** @var string */
    private $organization;

    /** @var string */
    private $username;

    /** @var string */
    private $password;

    /**
     * Credentials constructor.
     * @param string $organization
     * @param string $username
     * @param string $password
     */
    public function __construct($organization, $username, $password)
    {
        $this->organization = $organization;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }
}
