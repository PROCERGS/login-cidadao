<?php

namespace PROCERGS\LoginCidadao\NotificationBundle\Model;

use PROCERGS\OAuthBundle\Model\ClientInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;

class ClientSettings
{

    protected $client;
    protected $options;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
        $this->options = new ArrayCollection();
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function setClient($client)
    {
        $this->client = $client;
        return $this;
    }

    public function persist(EntityManager $em)
    {
        foreach ($this->getOptions() as $option) {
            $em->persist($option);
        }
    }

}
