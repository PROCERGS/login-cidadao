<?php

namespace LoginCidadao\OAuthBundle\Entity;

use LoginCidadao\CoreBundle\Entity\Authorization;
use FOS\OAuthServerBundle\Entity\Client as BaseClient;
use Doctrine\ORM\Mapping as ORM;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation as JMS;
use OAuth2\OAuth2;
use Doctrine\Common\Collections\ArrayCollection;
use LoginCidadao\CoreBundle\Entity\Person;
use LoginCidadao\CoreBundle\Model\AbstractUniqueEntity;
use LoginCidadao\CoreBundle\Model\UniqueEntityInterface;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass="LoginCidadao\OAuthBundle\Entity\ClientRepository")
 * @ORM\Table(name="client")
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity("name")
 * @JMS\ExclusionPolicy("all")
 * @Vich\Uploadable
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
     * @ORM\Column(type="json_array", nullable=false)
     */
    protected $allowedScopes;

    /**
     * @ORM\OneToMany(targetEntity="LoginCidadao\CoreBundle\Entity\Authorization", mappedBy="client", cascade={"remove"}, orphanRemoval=true)
     */
    protected $authorizations;

    /**
     * @ORM\Column(type="string", length=2000)
     * @JMS\Expose
     * @JMS\Groups({"public"})
     */
    protected $siteUrl;

    /**
     * @Assert\File(
     *      maxSize="2M",
     *      maxSizeMessage="The maxmimum allowed file size is 2MB.",
     *      mimeTypes={"image/png", "image/jpeg", "image/pjpeg"},
     *      mimeTypesMessage="Only JPEG and PNG images are allowed."
     * )
     * @Vich\UploadableField(mapping="client_image", fileNameProperty="imageName")
     * @var File $image
     * @JMS\Since("1.0.2")
     */
    protected $image;

    /**
     * @ORM\Column(type="string", length=255, name="image_name", nullable=true)
     *
     * @var string $imageName
     * @JMS\Since("1.0.2")
     */
    protected $imageName;

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
     * @ORM\ManyToMany(targetEntity="LoginCidadao\CoreBundle\Entity\Person", inversedBy="clients"  )
     * @ORM\JoinTable(name="client_owners",
     *      joinColumns={@ORM\JoinColumn(name="person_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="client_id", referencedColumnName="id")}
     *      )
     * @var ArrayCollection
     */
    protected $owners;

    /**
     * @ORM\OneToMany(targetEntity="LoginCidadao\APIBundle\Entity\LogoutKey", mappedBy="client")
     */
    protected $logoutKeys;

    /**
     * @var \LoginCidadao\OpenIDBundle\Entity\ClientMetadata
     * @ORM\OneToOne(targetEntity="LoginCidadao\OpenIDBundle\Entity\ClientMetadata", mappedBy="client", cascade={"persist"})
     */
    protected $metadata;

    /**
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected $updatedAt;
    /**
     * @ORM\Column(type="string", nullable=true, unique=true)
     * @var string
     */
    private $uid;

    public function __construct()
    {
        parent::__construct();
        $this->authorizations = new ArrayCollection();
        $this->owners = new ArrayCollection();

        $this->allowedScopes = array(
            'public_profile',
            'openid',
        );
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

    public function getName()
    {
        if ($this->getMetadata()) {
            if ($this->getMetadata()->getClientName() === null &&
                $this->name !== null
            ) {
                $this->getMetadata()->setClientName($this->name);
            }

            return $this->getMetadata()->getClientName();
        }

        return $this->name;
    }

    public function setName($name)
    {
        if ($this->getMetadata()) {
            $this->getMetadata()->setClientName($name);
        }
        $this->name = $name;

        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getSiteUrl()
    {
        if ($this->getMetadata()) {
            return $this->getMetadata()->getClientUri();
        }

        return $this->siteUrl;
    }

    public function setSiteUrl($url)
    {
        if ($this->getMetadata()) {
            $this->getMetadata()->setClientUri($url);
        }
        $this->siteUrl = $url;

        return $this;
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

    public function getLandingPageUrl()
    {
        if ($this->getMetadata()) {
            return $this->getMetadata()->getInitiateLoginUri();
        }

        return $this->landingPageUrl;
    }

    public function setLandingPageUrl($landingPageUrl)
    {
        if ($this->getMetadata()) {
            $this->getMetadata()->setInitiateLoginUri($landingPageUrl);
        }
        $this->landingPageUrl = $landingPageUrl;

        return $this;
    }

    public function getTermsOfUseUrl()
    {
        if ($this->getMetadata()) {
            return $this->getMetadata()->getTosUri();
        }

        return $this->termsOfUseUrl;
    }

    public function setTermsOfUseUrl($termsOfUseUrl)
    {
        if ($this->getMetadata()) {
            $this->getMetadata()->setTosUri($termsOfUseUrl);
        }
        $this->termsOfUseUrl = $termsOfUseUrl;

        return $this;
    }

    public function getAllowedScopes()
    {
        $scopes = $this->allowedScopes;

        if (!is_array($scopes)) {
            $scopes = array('public_profile');
        }

        return $scopes;
    }

    public function setAllowedScopes(array $allowedScopes)
    {
        $this->allowedScopes = $allowedScopes;

        return $this;
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

    /* Unique Interface Stuff */

    public function setOwners(ArrayCollection $owners)
    {
        $this->owners = $owners;

        return $this;
    }

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

    /**
     * Compatibility with OIDC code
     */
    public function getClientId()
    {
        return $this->getPublicId();
    }

    /**
     * Compatibility with OIDC code
     */
    public function getClientSecret()
    {
        return $this->getSecret();
    }

    public function getMetadata()
    {
        return $this->metadata;
    }

    public function setMetadata(\LoginCidadao\OpenIDBundle\Entity\ClientMetadata $metadata)
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function getRedirectUris()
    {
        if ($this->getMetadata()) {
            return $this->getMetadata()->getRedirectUris();
        }

        return parent::getRedirectUris();
    }

    public function setRedirectUris(array $redirectUris)
    {
        if ($this->getMetadata()) {
            $this->getMetadata()->setRedirectUris($redirectUris);
        } else {
            parent::setRedirectUris($redirectUris);
        }

        return $this;
    }

    /**
     * @return File
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the  update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile $image
     */
    public function setImage($image)
    {
        $this->image = $image;

        if ($this->image) {
            $this->updatedAt = new \DateTime('now');
        }
    }

    /**
     * @return string
     */
    public function getImageName()
    {
        return $this->imageName;
    }

    /**
     * @param string $imageName
     */
    public function setImageName($imageName)
    {
        $this->imageName = $imageName;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function setUpdatedAt($updatedAt = null)
    {
        if ($updatedAt instanceof \DateTime) {
            $this->updatedAt = $updatedAt;
        } else {
            $this->updatedAt = new \DateTime('now');
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedGrantTypes()
    {
        if ($this->getMetadata()) {
            return $this->getMetadata()->getGrantTypes();
        }

        return parent::getAllowedGrantTypes();
    }

    public function ownsDomain($domain)
    {
        foreach ($this->getRedirectUris() as $redirectUrl) {
            $host = parse_url($redirectUrl, PHP_URL_HOST);
            if ($host == $domain) {
                return true;
            }
        }

        return false;
    }

    public function getContacts()
    {
        if ($this->getMetadata()) {
            return $this->getMetadata()->getContacts();
        }

        return array_map(
            function (PersonInterface $owner) {
                return $owner->getEmail();
            },
            $this->getOwners()->toArray()
        );
    }
}
