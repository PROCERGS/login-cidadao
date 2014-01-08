<?php

namespace PROCERGS\OAuthBundle\Entity;

use FOS\OAuthServerBundle\Entity\Client as BaseClient;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Client extends BaseClient
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @ORM\Column(type="string")
     */
    protected $description;

    /**
     * @ORM\OneToMany(targetEntity="PROCERGS\LoginCidadao\CoreBundle\Entity\Authorization", mappedBy="client")
     */
    protected $people;

    /**
     * @ORM\Column(type="string")
     */
    protected $siteUrl;

    public function __construct()
    {
        parent::__construct();
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setSiteUrl($url)
    {
        $this->siteUrl = $url;
    }

    public function getSiteUrl()
    {
        return $this->siteUrl;
    }

}
