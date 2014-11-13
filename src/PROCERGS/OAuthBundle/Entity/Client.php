<?php

namespace PROCERGS\OAuthBundle\Entity;

use PROCERGS\LoginCidadao\CoreBundle\Entity\Authorization;
use FOS\OAuthServerBundle\Entity\Client as BaseClient;
use Doctrine\ORM\Mapping as ORM;
use PROCERGS\LoginCidadao\NotificationBundle\Entity\Notification;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Tests\Fixtures\Publisher;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation as JMS;
use OAuth2\OAuth2;
use Doctrine\Common\Collections\ArrayCollection;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Person;
use PROCERGS\LoginCidadao\CoreBundle\Model\AbstractUniqueEntity;
use PROCERGS\LoginCidadao\CoreBundle\Model\UniqueEntityInterface;
use PROCERGS\OAuthBundle\Model\ClientInterface;

/**
 * @ORM\Entity(repositoryClass="PROCERGS\OAuthBundle\Entity\ClientRepository")
 * @ORM\Table(name="client")
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity("name")
 * @JMS\ExclusionPolicy("all")
 */
class Client extends BaseClient implements UniqueEntityInterface, ClientInterface
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\Expose
     * @JMS\Groups({"public"})
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=false, unique=true)
     * @JMS\Expose
     * @JMS\Groups({"public"})
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=4000, nullable=false)
     * @JMS\Expose
     * @JMS\Groups({"public"})
     */
    protected $description;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $maxNotificationLevel;

    /**
     * @ORM\Column(type="string", length=2000, nullable=true)
     * @JMS\Expose
     * @JMS\Groups({"public"})
     */
    protected $landingPageUrl;

    /**
     * @ORM\Column(type="string", length=2000, nullable=false)
     * @JMS\Expose
     * @JMS\Groups({"public"})
     */
    protected $termsOfUseUrl;

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    protected $allowedScopes;

    /**
     * @ORM\OneToMany(targetEntity="PROCERGS\LoginCidadao\CoreBundle\Entity\Authorization", mappedBy="client", cascade={"remove"}, orphanRemoval=true)
     */
    protected $authorizations;

    /**
     * @ORM\Column(type="string", length=2000)
     * @JMS\Expose
     * @JMS\Groups({"public"})
     */
    protected $siteUrl;

    /**
     * @ORM\OneToMany(targetEntity="PROCERGS\LoginCidadao\NotificationBundle\Entity\Notification", mappedBy="sender")
     */
    protected $notifications;

    /**
     * @ORM\OneToMany(targetEntity="PROCERGS\LoginCidadao\NotificationBundle\Entity\Category", mappedBy="client")
     */
    protected $categories;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $picturePath;
    protected $tempPicturePath;

    /**
     * @Assert\File(maxSize="6000000")
     */
    protected $pictureFile;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @JMS\Expose
     * @JMS\Groups({"public"})
     */
    protected $published;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @JMS\Expose
     * @JMS\Groups({"public"})
     */
    protected $visible;

    /**
     * @ORM\ManyToMany(targetEntity="PROCERGS\LoginCidadao\CoreBundle\Entity\Person", inversedBy="clients"  )
     * @ORM\JoinTable(name="client_owners",
     *      joinColumns={@ORM\JoinColumn(name="person_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="client_id", referencedColumnName="id")}
     *      )
     * @var ArrayCollection
     */
    protected $owners;

    /**
     * @ORM\OneToMany(targetEntity="PROCERGS\LoginCidadao\APIBundle\Entity\LogoutKey", mappedBy="client")
     */
    protected $logoutKeys;

    public function __construct()
    {
        parent::__construct();
        $this->authorizations = new ArrayCollection();
        $this->owners = new ArrayCollection();
        $this->maxNotificationLevel = Notification::LEVEL_NORMAL;
    }

    public static function getAllGrants()
    {
        return array(
            OAuth2::GRANT_TYPE_AUTH_CODE,
            OAuth2::GRANT_TYPE_IMPLICIT,
            OAuth2::GRANT_TYPE_USER_CREDENTIALS,
            OAuth2::GRANT_TYPE_CLIENT_CREDENTIALS,
            OAuth2::GRANT_TYPE_REFRESH_TOKEN,
            OAuth2::GRANT_TYPE_EXTENSIONS,
        );
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

    public function getLandingPageUrl()
    {
        return $this->landingPageUrl;
    }

    public function setLandingPageUrl($landingPageUrl)
    {
        $this->landingPageUrl = $landingPageUrl;
        return $this;
    }

    public function getTermsOfUseUrl()
    {
        return $this->termsOfUseUrl;
    }

    public function setTermsOfUseUrl($termsOfUseUrl)
    {
        $this->termsOfUseUrl = $termsOfUseUrl;
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

    public function getAbsolutePicturePath()
    {
        return null === $this->picturePath ? null : $this->getPictureUploadRootDir() . DIRECTORY_SEPARATOR . $this->picturePath;
    }

    public function getPictureWebPath()
    {
        return self::resolvePictureWebPath($this->picturePath);
    }

    protected function getPictureUploadRootDir()
    {
        return __DIR__ . '/../../../../web/' . self::getPictureUploadDir();
    }

    protected static function getPictureUploadDir()
    {
        return 'uploads/client-pictures';
    }
    
    public static function resolvePictureWebPath($var)
    {
        return null === $var ? null : self::getPictureUploadDir() . '/' . $var;
    }

    public function setPictureFile(File $pictureFile = null)
    {
        $this->pictureFile = $pictureFile;
        if (isset($this->picturePath)) {
            $this->tempPicturePath = $this->picturePath;
            $this->picturePath = null;
        } else {
            $this->picturePath = null;
        }
    }

    /**
     *
     * @return File
     */
    public function getPictureFile()
    {
        return $this->pictureFile;
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function uploadPicture()
    {
        if (null === $this->getPictureFile()) {
            return;
        }

        $this->getPictureFile()->move(
            $this->getPictureUploadRootDir(), $this->picturePath
        );

        if (isset($this->tempPicturePath) && $this->tempPicturePath != $this->picturePath) {
            @unlink($this->getPictureUploadRootDir() . DIRECTORY_SEPARATOR . $this->tempPicturePath);
            $this->tempPicturePath = null;
        }

        $this->pictureFile = null;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        if (null !== $this->getPictureFile()) {
            $filename = sha1($this->getId());
            $this->picturePath = "$filename." . $this->getPictureFile()->guessExtension();
        }
    }

    /**
     * @ORM\PostRemove()
     */
    public function removePicturePostRemoval()
    {
        if ($file = $this->getAbsolutePicturePath()) {
            unlink($file);
        }
    }

    public function isVisible()
    {
        return $this->visible;
    }

    public function setVisible($visible)
    {
        $this->visible = $visible;

        return $this;
    }

    public function isPublished()
    {
        return $this->published;
    }

    public function setPublished($published)
    {
        $this->published = $published;

        return $this;
    }

    public function setId($var)
    {
        $this->id = $var;
        return $this;
    }

    public function getCategories()
    {
        return $this->categories;
    }

    public function getOwners()
    {
        return $this->owners;
    }

    public function setOwners(ArrayCollection $owners)
    {
        $this->owners = $owners;
        return $this;
    }

    /* Unique Interface Stuff */

    /**
     * @ORM\Column(type="string", nullable=true, unique=true)
     * @var string
     */
    private $uid;

    /**
     * Gets the Unique Id of the Entity.
     * @return string the entity UID
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * Sets the Unique Id of the Entity.
     * @param string $id the entity UID
     * @return AbstractUniqueEntity
     */
    public function setUid($uid = null)
    {
        $this->uid = $uid;

        return $this;
    }

}
