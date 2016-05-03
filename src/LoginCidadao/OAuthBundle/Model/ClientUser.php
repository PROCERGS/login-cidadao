<?php

namespace LoginCidadao\OAuthBundle\Model;

use Symfony\Component\Security\Core\User\UserInterface;

class ClientUser implements UserInterface
{

    /**
     * @var ClientInterface
     */
    protected $client;

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
        return array('ROLE_API_CLIENT');
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
