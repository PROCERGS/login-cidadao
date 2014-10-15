<?php

namespace PROCERGS\LoginCidadao\NotificationBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;

class NotificationSettings
{

    protected $clients;

    public function __construct()
    {
        $this->clients = new ArrayCollection();
    }

    public function getClients()
    {
        return $this->clients;
    }

    public function setClients($clients)
    {
        $this->clients = $clients;
        return $this;
    }

    public function persist(EntityManager $em)
    {
        foreach ($this->getClients() as $client) {
            $client->persist($em);
        }
    }
}
