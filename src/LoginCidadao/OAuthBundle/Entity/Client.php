<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OAuthBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use LoginCidadao\CoreBundle\Entity\Authorization;
use FOS\OAuthServerBundle\Entity\Client as BaseClient;
use Doctrine\ORM\Mapping as ORM;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\OpenIDBundle\Entity\ClientMetadata;
use LoginCidadao\RemoteClaimsBundle\Model\ClaimProviderInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation as JMS;
use OAuth2\OAuth2;
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
class Client extends BaseClient implements ClientInterface, ClaimProviderInterface
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
     * @JMS\SerializedName("client_name")
     * @JMS\Groups({"public", "remote_claim"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=4000, nullable=true)
     * @JMS\Expose
     * @JMS\Groups({"public"})
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=2000, nullable=true)
     * @JMS\Expose
     * @JMS\Groups({"public"})
     */
    private $landingPageUrl;

    /**
     * @ORM\Column(type="string", length=2000, nullable=true)
     * @JMS\Expose
     * @JMS\Groups({"public"})
     */
    private $termsOfUseUrl;

    /**
     * @var string[]|null
     * @ORM\Column(type="json_array", nullable=false)
     */
    private $allowedScopes;

    /**
     * @ORM\OneToMany(targetEntity="LoginCidadao\CoreBundle\Entity\Authorization", mappedBy="client", cascade={"remove"}, orphanRemoval=true)
     * @var Authorization[]|ArrayCollection
     */
    private $authorizations;

    /**
     * @ORM\Column(type="string", length=2000, nullable=true)
     * @JMS\Expose
     * @JMS\Groups({"public"})
     */
    private $siteUrl;

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
    private $image;

    /**
     * @ORM\Column(type="string", length=255, name="image_name", nullable=true)
     *
     * @var string $imageName
     * @JMS\Since("1.0.2")
     */
    private $imageName;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @JMS\Expose
     * @JMS\Groups({"public"})
     */
    private $published;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @JMS\Expose
     * @JMS\Groups({"public"})
     */
    private $visible;

    /**
     * @ORM\ManyToMany(targetEntity="LoginCidadao\CoreBundle\Entity\Person", inversedBy="clients"  )
     * @ORM\JoinTable(name="client_owners",
     *      joinColumns={@ORM\JoinColumn(name="person_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="client_id", referencedColumnName="id")}
     *      )
     * @var PersonInterface[]|ArrayCollection
     */
    private $owners;

    /**
     * @ORM\OneToMany(targetEntity="LoginCidadao\APIBundle\Entity\LogoutKey", mappedBy="client")
     */
    private $logoutKeys;

    /**
     * @var \LoginCidadao\OpenIDBundle\Entity\ClientMetadata
     * @ORM\OneToOne(targetEntity="LoginCidadao\OpenIDBundle\Entity\ClientMetadata", mappedBy="client", cascade={"persist"})
     */
    private $metadata;

    /**
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;
    /**
     * @ORM\Column(type="string", nullable=true, unique=true)
     * @var string
     */
    private $uid;

    public function __construct()
    {
        parent::__construct();
        $this->authorizations = [];
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

    /**
     * @return string
     */
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

        return $this;
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

    /**
     * @param array|Authorization[]|ArrayCollection $authorizations
     * @return $this
     */
    public function setAuthorizations($authorizations = [])
    {
        $this->authorizations = $authorizations;

        return $this;
    }

    /**
     * @return array|Authorization[]
     */
    public function getAuthorizations()
    {
        return $this->authorizations;
    }

    /**
     * @param Authorization $authorization
     */
    public function removeAuthorization(Authorization $authorization)
    {
        foreach ($this->authorizations as $k => $candidate) {
            if ($candidate->getId() === $authorization->getId()) {
                unset($this->authorizations[$k]);
            }
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
        $scopes = ['public_profile', 'openid'];

        if (is_array($this->allowedScopes)) {
            $scopes = $this->allowedScopes;
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

    public function getOwners()
    {
        return $this->owners;
    }

    /* Unique Interface Stuff */

    public function setOwners($owners)
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
     * @param string $uid the entity UID
     * @return ClientInterface
     */
    public function setUid($uid = null)
    {
        $this->uid = $uid;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setClientId($clientId)
    {
        $parts = explode('_', $clientId, 2);
        $this->setId($parts[0]);
        $this->setRandomId($parts[1]);

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

    public function setMetadata(ClientMetadata $metadata)
    {
        $this->metadata = $metadata;

        return $this;
    }

    /**
     * @JMS\VirtualProperty()
     * @JMS\SerializedName("redirect_uris")
     * @JMS\Groups({"remote_claim"})
     * @return array|string[]
     */
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
     * @return $this
     */
    public function setImage($image)
    {
        $this->image = $image;

        if ($this->image) {
            $this->updatedAt = new \DateTime('now');
        }

        return $this;
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
     * @return $this
     */
    public function setImageName($imageName)
    {
        $this->imageName = $imageName;

        return $this;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     * @param \DateTime|null $updatedAt
     * @return $this
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
            $this->getOwners()
        );
    }

    /**
     * @JMS\VirtualProperty()
     * @JMS\SerializedName("client_id")
     * @JMS\Groups({"remote_claim"})
     * @inheritDoc
     */
    public function getPublicId()
    {
        return parent::getPublicId();
    }

    /**
     * @JMS\VirtualProperty()
     * @JMS\SerializedName("client_uri")
     * @JMS\Groups({"remote_claim"})
     */
    public function getClientUri()
    {
        return $this->getSiteUrl();
    }
}
