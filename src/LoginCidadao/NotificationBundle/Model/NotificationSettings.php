<?php

namespace LoginCidadao\NotificationBundle\Model;

use LoginCidadao\NotificationBundle\Entity\PersonNotificationOption;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;

/**
 * This is an intermetiate class to better organize the FormView generation.
 *
 * This class stores ClientSettings objects which then store
 * PersonNotificationOptions.
 */
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

    public function addOption(PersonNotificationOption $option)
    {
        $client = $option->getCategory()->getClient();
        foreach ($this->getClients()->getValues() as $clientSettings) {
            $existingId = $clientSettings->getClient()->getId();
            $newId = $client->getId();
            if ($existingId === $newId) {
                $clientSettings->getOptions()->add($option);
                return true;
            }
        }
        // This is a new client
        $clientSettings = new ClientSettings($client);
        $clientSettings->getOptions()->add($option);
        $this->getClients()->add($clientSettings);
    }

}
