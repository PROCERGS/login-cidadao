<?php

namespace LoginCidadao\NotificationBundle\Model;

use LoginCidadao\OAuthBundle\Model\ClientInterface;
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

    /**
     * @return ArrayCollection
     */
    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions(ArrayCollection $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @return ClientInterface
     */
    public function getClient()
    {
        return $this->client;
    }

    public function setClient(ClientInterface $client)
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
