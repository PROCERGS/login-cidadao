<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OAuthBundle\Model;

use Symfony\Component\Security\Core\User\UserInterface;

class ClientUser implements UserInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function eraseCredentials()
    {
        return;
    }

    public function getPassword()
    {
        return null;
    }

    public function getRoles()
    {
        return ['ROLE_API_CLIENT'];
    }

    public function getSalt()
    {
        return null;
    }

    public function getUsername()
    {
        return sprintf("client:%s", $this->getClient()->getPublicId());
    }

    /**
     * @return ClientInterface
     */
    public function getClient()
    {
        return $this->client;
    }
}
