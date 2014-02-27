<?php

namespace PROCERGS\OAuthBundle\Entity;

use PROCERGS\LoginCidadao\CoreBundle\Entity\Authorization;
use FOS\OAuthServerBundle\Entity\Client as BaseClient;
use Doctrine\ORM\Mapping as ORM;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Notification;

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
     * @ORM\Column(type="integer")
     */
    protected $maxNotificationLevel;

    /**
     * @ORM\OneToMany(targetEntity="PROCERGS\LoginCidadao\CoreBundle\Entity\Authorization", mappedBy="client", cascade={"remove"}, orphanRemoval=true)
     */
    protected $authorizations;

    /**
     * @ORM\Column(type="string")
     */
    protected $siteUrl;

    /**
     * @ORM\OneToMany(targetEntity="PROCERGS\LoginCidadao\CoreBundle\Entity\Notification", mappedBy="client")
     */
    protected $notifications;

    public function __construct()
    {
        parent::__construct();
        $this->authorizations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->maxNotificationLevel = Notification::LEVEL_NORMAL;
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

    public function getAuthorizations()
    {
        return $this->authorizations;
    }

    public function removeAuthorization(Authorization $authorization)
    {
        if ($this->authorizations->contains($authorization)) {
            $this->authorizations->removeElement($authorization);
        }
    }

    public function getMaxNotificationLevel()
    {
        return $this->maxNotificationLevel;
    }

    public function setMaxNotificationLevel($maxNotificationLevel)
    {
        $this->maxNotificationLevel = $maxNotificationLevel;

        return $this;
    }
}
