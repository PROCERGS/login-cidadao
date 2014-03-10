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
     * @ORM\Column(type="string", nullable=false)
     */
    protected $name;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $description;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $maxNotificationLevel;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $landingPageURL;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $termsOfUseURL;

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    protected $allowedScopes;

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

    public function getLandingPageURL()
    {
        return $this->landingPageURL;
    }

    public function setLandingPageURL($landingPageURL)
    {
        $this->landingPageURL = $landingPageURL;
        return $this;
    }

    public function getTermsOfUseURL()
    {
        return $this->termsOfUseURL;
    }

    public function setTermsOfUseURL($termsOfUseURL)
    {
        $this->termsOfUseURL = $termsOfUseURL;
        return $this;
    }

    public function getAllowedScopes()
    {
        return $this->allowedScopes;
    }

    public function setAllowedScopes(array $allowedScopes)
    {
        $this->allowedScopes = $allowedScopes;

        return $this;
    }

}
