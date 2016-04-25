<?php

namespace LoginCidadao\CoreBundle\Entity;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;
use Scheb\TwoFactorBundle\Model\BackupCodeInterface;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use JMS\Serializer\Annotation as JMS;
use FOS\UserBundle\Model\User as BaseUser;
use LoginCidadao\OAuthBundle\Entity\Client;
use LoginCidadao\CoreBundle\Model\SelectData;
use LoginCidadao\LongPolling\LongPollingUtils;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use LoginCidadao\CoreBundle\Model\LocationAwareInterface;
use LoginCidadao\ValidationBundle\Validator\Constraints as LCAssert;
use Donato\PathWellBundle\Validator\Constraints\PathWell;

/**
 * @ORM\Entity(repositoryClass="LoginCidadao\CoreBundle\Entity\PersonRepository")
 * @ORM\Table(name="person")
 * @UniqueEntity("cpf")
 * @UniqueEntity("username")
 * @ORM\HasLifecycleCallbacks
 * @JMS\ExclusionPolicy("all")
 * @Vich\Uploadable
 */
class Person extends BaseUser implements PersonInterface, TwoFactorInterface, BackupCodeInterface,
    LocationAwareInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\Since("1.0")
     */
    protected $id;

    /**
     * @JMS\Expose
     * @JMS\Groups({"first_name","full_name","public_profile","given_name"})
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank(message="Please enter your name.", groups={"Profile"})
     * @Assert\Length(
     *     min=3,
     *     max="255",
     *     minMessage="The name is too short.",
     *     maxMessage="The name is too long.",
     *     groups={"Registration", "Profile"}
     * )
     * @JMS\Since("1.0")
     */
    protected $firstName;

    /**
     * @JMS\Expose
     * @JMS\Groups({"last_name","full_name","family_name","middle_name","name"})
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank(message="Please enter your surname.", groups={"Profile"})
     * @Assert\Length(
     *     min=1,
     *     max="255",
     *     minMessage="The surname is too short.",
     *     maxMessage="The surname is too long.",
     *     groups={"Registration", "Profile"}
     * )
     * @JMS\Since("1.0")
     */
    protected $surname;

    /**
     * @JMS\Expose
     * @JMS\Groups({"username","preferred_username"})
     * @LCAssert\Username
     * @Assert\NotBlank
     * @Assert\Length(
     *     min="1",
     *     max="33",
     *     groups={"Registration", "Profile"}
     * )
     * @JMS\Since("1.0")
     */
    protected $username;

    /**
     * @JMS\Exclude
     * @PathWell(
     *     groups={"Registration", "ResetPassword", "ChangePassword", "LoginCidadaoRegistration"}
     * )
     * @Assert\Length(
     *     min=8,
     *     max=4096,
     *     groups={"Registration", "ResetPassword", "ChangePassword", "LoginCidadaoRegistration"}
     * )
     */
    protected $plainPassword;

    /**
     * @JMS\Expose
     * @JMS\Groups({"cpf"})
     * @ORM\Column(type="string", nullable=true, unique=true)
     * @LCAssert\CPF
     * @JMS\Since("1.0")
     */
    protected $cpf;

    /**
     * @JMS\Expose
     * @JMS\Groups({"email"})
     * @JMS\Since("1.0")
     */
    protected $email;

    /**
     * @JMS\Expose
     * @JMS\Groups({"birthdate"})
     * @ORM\Column(type="date", nullable=true)
     * @JMS\Since("1.0")
     */
    protected $birthdate;

    /**
     * @ORM\Column(name="cpf_expiration", type="date", nullable=true)
     * @JMS\Since("1.0")
     */
    protected $cpfExpiration;

    /**
     * @ORM\Column(name="email_expiration", type="datetime", nullable=true)
     * @JMS\Since("1.0")
     */
    protected $emailExpiration;

    /**
     * @JMS\Expose
     * @JMS\Groups({"mobile","phone_number"})
     * @ORM\Column(type="string", nullable=true)
     * @JMS\Since("1.0")
     */
    protected $mobile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     * @JMS\Since("1.0")
     */
    protected $twitterPicture;

    /**
     * @JMS\Expose
     * @JMS\Groups({"city"})
     * @ORM\ManyToOne(targetEntity="LoginCidadao\CoreBundle\Entity\City")
     * @ORM\JoinColumn(name="city_id", referencedColumnName="id")
     * @JMS\Since("1.0")
     */
    protected $city;

    /**
     * @var string
     *
     * @ORM\Column(name="facebookId", type="string", length=255, nullable=true, unique=true)
     * @JMS\Since("1.0")
     */
    protected $facebookId;

    /**
     * @var string
     *
     * @ORM\Column(name="facebookUsername", type="string", length=255, nullable=true)
     * @JMS\Since("1.0")
     */
    protected $facebookUsername;

    /**
     * @var string
     *
     * @ORM\Column(name="facebookAccessToken", type="string", length=255, nullable=true)
     * @JMS\Since("1.1")
     */
    protected $facebookAccessToken;

    /**
     * @var string
     *
     * @ORM\Column(name="twitterId", type="string", length=255, nullable=true, unique=true)
     * @JMS\Since("1.0")
     */
    protected $twitterId;

    /**
     * @var string
     *
     * @ORM\Column(name="twitterUsername", type="string", length=255, nullable=true)
     * @JMS\Since("1.0")
     */
    protected $twitterUsername;

    /**
     * @var string
     *
     * @ORM\Column(name="twitterAccessToken", type="string", length=255, nullable=true)
     * @JMS\Since("1.0")
     */
    protected $twitterAccessToken;

    /**
     * @ORM\OneToMany(targetEntity="Authorization", mappedBy="person", cascade={"remove"}, orphanRemoval=true)
     */
    protected $authorizations;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     * @var \DateTime
     * @JMS\Since("1.0")
     */
    protected $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     * @JMS\Since("1.0")
     */
    protected $emailConfirmedAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     * @JMS\Since("1.0")
     */
    protected $previousValidEmail;

    /**
     * @ORM\OneToMany(targetEntity="LoginCidadao\NotificationBundle\Entity\Notification", mappedBy="person")
     */
    protected $notifications;

    /**
     * @ORM\OneToMany(targetEntity="LoginCidadao\NotificationBundle\Entity\Broadcast", mappedBy="person")
     */
    protected $broadcasts;

    /**
     * @ORM\ManyToMany(targetEntity="LoginCidadao\OAuthBundle\Entity\Client", mappedBy="owners")
     */
    protected $clients;

    /**
     * @ORM\OneToMany(targetEntity="ClientSuggestion", mappedBy="person")
     */
    protected $suggestions;

    /**
     * @JMS\Expose
     * @JMS\Groups({"state"})
     * @ORM\ManyToOne(targetEntity="LoginCidadao\CoreBundle\Entity\State")
     * @ORM\JoinColumn(name="state_id", referencedColumnName="id")
     * @JMS\Since("1.0.2")
     */
    protected $state;

    /**
     * @JMS\Expose
     * @JMS\Groups({"country"})
     * @ORM\ManyToOne(targetEntity="LoginCidadao\CoreBundle\Entity\Country")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id")
     * @JMS\Since("1.0.2")
     */
    protected $country;

    /**
     * @Assert\File(
     *      maxSize="2M",
     *      maxSizeMessage="The maxmimum allowed file size is 2MB.",
     *      mimeTypes={"image/png", "image/jpeg", "image/pjpeg"},
     *      mimeTypesMessage="Only JPEG and PNG images are allowed."
     * )
     * @Vich\UploadableField(mapping="user_image", fileNameProperty="imageName")
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
     * @JMS\Expose
     * @JMS\Groups({"public_profile","picture"})
     * @JMS\Since("1.0.2")
     */
    protected $profilePictureUrl;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @JMS\Expose
     * @JMS\Groups({"public_profile","updated_at"})
     * @var \DateTime $updatedAt
     * @JMS\Since("1.0.2")
     */
    protected $updatedAt;

    /**
     * @var string
     *
     * @ORM\Column(name="googleId", type="string", length=255, nullable=true, unique=true)
     * @JMS\Since("1.0.3")
     */
    protected $googleId;

    /**
     * @var string
     *
     * @ORM\Column(name="googleUsername", type="string", length=255, nullable=true)
     * @JMS\Since("1.0.3")
     */
    protected $googleUsername;

    /**
     * @var string
     *
     * @ORM\Column(name="googleAccessToken", type="string", length=255, nullable=true)
     * @JMS\Since("1.0.3")
     */
    protected $googleAccessToken;

    /**
     * @JMS\Expose
     * @JMS\Groups({"id_cards"})
     * @ORM\OneToMany(targetEntity="LoginCidadao\CoreBundle\Entity\IdCard", mappedBy="person")
     * @JMS\Since("1.0.3")
     */
    protected $idCards;

    /**
     * @ORM\OneToMany(targetEntity="LoginCidadao\NotificationBundle\Entity\NotificationToken", mappedBy="person")
     */
    protected $notificationTokens;

    /**
     * @ORM\OneToMany(targetEntity="LoginCidadao\NotificationBundle\Entity\PersonNotificationOption", mappedBy="person")
     */
    protected $notificationOptions;

    /**
     * @JMS\Expose
     * @JMS\Groups({"public_profile"})
     * @var array
     */
    protected $badges = array();

    /**
     * @ORM\OneToMany(targetEntity="LoginCidadao\APIBundle\Entity\LogoutKey", mappedBy="person", cascade={"remove"}, orphanRemoval=true)
     */
    protected $logoutKeys;

    /**
     * @JMS\Expose
     * @JMS\Groups({"addresses","address"})
     * @ORM\OneToMany(targetEntity="LoginCidadao\CoreBundle\Entity\PersonAddress", mappedBy="person", cascade={"remove"}, orphanRemoval=true)
     */
    protected $addresses;

    /**
     * @ORM\Column(name="google_authenticator_secret", type="string", nullable=true)
     */
    protected $googleAuthenticatorSecret;

    /**
     * @JMS\Expose
     * @JMS\Groups({"nationality"})
     * @ORM\ManyToOne(targetEntity="LoginCidadao\CoreBundle\Entity\Country")
     * @ORM\JoinColumn(name="nationality_id", referencedColumnName="id")
     * @JMS\Since("1.0.2")
     */
    protected $nationality;

    /**
     * @JMS\Exclude
     * @ORM\OneToMany(targetEntity="BackupCode", mappedBy="person", cascade={"remove"}, orphanRemoval=true)
     */
    protected $backupCodes;

    /**
     * @JMS\Exclude
     * @ORM\Column(name="password_encoder_name", type="string", length=255, nullable=true)
     */
    protected $passwordEncoderName;

    public function __construct()
    {
        parent::__construct();
        $this->authorizations      = new ArrayCollection();
        $this->notificationTokens  = new ArrayCollection();
        $this->notificationOptions = new ArrayCollection();
        $this->notifications       = new ArrayCollection();
        $this->clients             = new ArrayCollection();
        $this->logoutKeys          = new ArrayCollection();
        $this->addresses           = new ArrayCollection();
        $this->backupCodes         = new ArrayCollection();
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getSurname()
    {
        return $this->surname;
    }

    public function setSurname($suname)
    {
        $this->surname = $suname;

        return $this;
    }

    public function getBirthdate()
    {
        return $this->birthdate;
    }

    public function setBirthdate($birthdate)
    {
        $this->birthdate = $birthdate;
    }

    public function getMobile()
    {
        return $this->mobile;
    }

    public function setMobile($mobile)
    {
        $mobile       = preg_replace('/[^0-9]/', '', $mobile);
        $this->mobile = $mobile;
    }

    public function addAuthorization(Authorization $authorization)
    {
        $this->authorizations->add($authorization);
        $authorization->setPerson($this);
        return $this;
    }

    public function removeAuthorization(Authorization $authorization)
    {
        if ($this->authorizations->contains($authorization)) {
            $this->authorizations->removeElement($authorization);
        }
        return $this;
    }

    /**
     * @return Authorization[]
     */
    public function getAuthorizations()
    {
        return $this->authorizations;
    }

    /**
     * Checks if a given Client can access this Person's specified scope.
     * @param \LoginCidadao\OAuthBundle\Entity\Client $client
     * @param mixed $scope can be a single scope or an array with several.
     * @return boolean
     */
    public function isAuthorizedClient(Client $client, $scope)
    {
        $authorizations = $this->getAuthorizations();
        foreach ($authorizations as $auth) {
            $c = $auth->getClient();
            if ($c->getId() == $client->getId()) {
                return $auth->hasScopes($scope);
            }
        }
        return false;
    }

    /**
     * @param Client $client
     * @return array
     */
    public function getClientScope(Client $client)
    {
        $authorizations = $this->getAuthorizations();
        foreach ($authorizations as $auth) {
            $c = $auth->getClient();
            if ($c->getId() == $client->getId()) {
                return $auth->getScope();
            }
        }
        return null;
    }

    /**
     * Checks if this Person has any authorization for a given Client.
     * WARNING: Note that it does NOT validate scope!
     * @param \LoginCidadao\OAuthBundle\Entity\Client | integer $client
     */
    public function hasAuthorization($client)
    {
        if ($client instanceof ClientInterface) {
            $id = $client->getId();
        } else {
            $id = $client;
        }
        $authorizations = $this->getAuthorizations();
        if (is_array($authorizations) || $authorizations instanceof Collection) {
            foreach ($authorizations as $auth) {
                $c = $auth->getClient();
                if ($c->getId() == $id) {
                    return true;
                }
            }
        }
        return false;
    }

    public function setFacebookId($facebookId)
    {
        $this->facebookId = $facebookId;

        return $this;
    }

    public function getFacebookId()
    {
        return $this->facebookId;
    }

    public function setTwitterId($twitterId)
    {
        $this->twitterId = $twitterId;

        return $this;
    }

    public function getTwitterId()
    {
        return $this->twitterId;
    }

    public function setTwitterUsername($twitterUsername)
    {
        $this->twitterUsername = $twitterUsername;

        return $this;
    }

    public function getTwitterUsername()
    {
        return $this->twitterUsername;
    }

    public function setTwitterAccessToken($twitterAccessToken)
    {
        $this->twitterAccessToken = $twitterAccessToken;

        return $this;
    }

    public function getTwitterAccessToken()
    {
        return $this->twitterAccessToken;
    }

    public function serialize()
    {
        return serialize(array($this->facebookId, parent::serialize()));
    }

    public function unserialize($data)
    {
        list($this->facebookId, $parentData) = unserialize($data);
        parent::unserialize($parentData);
    }

    /**
     * Get the full name of the user (first + last name)
     * @JMS\Groups({"full_name", "name"})
     * @JMS\VirtualProperty
     * @JMS\SerializedName("full_name")
     * @return string
     */
    public function getFullName()
    {
        $fullName = array();
        if ($this->getFirstname() !== null) {
            $fullName[] = $this->getFirstname();
        }
        if ($this->getSurname() !== null) {
            $fullName[] = $this->getSurname();
        }

        if (count($fullName) > 0) {
            return implode(' ', $fullName);
        } else {
            return null;
        }
    }

    /**
     * @JMS\Groups({"badges", "public_profile"})
     * @JMS\VirtualProperty
     * @JMS\SerializedName("deprecated_badges")
     * @return array
     */
    public function getDataValid()
    {
        $terms['cpf']   = is_numeric($this->cpf);
        $terms['email'] = is_null($this->getConfirmationToken());
        return $terms;
    }

    /**
     * @param array
     */
    public function setFBData($fbdata)
    {
        if (isset($fbdata['id'])) {
            $this->setFacebookId($fbdata['id']);
            $this->addRole('ROLE_FACEBOOK');
        }
        if (isset($fbdata['first_name']) && is_null($this->getFirstName())) {
            $this->setFirstName($fbdata['first_name']);
        }
        if (isset($fbdata['last_name']) && is_null($this->getSurname())) {
            $this->setSurname($fbdata['last_name']);
        }
        if (isset($fbdata['email']) && is_null($this->getEmail())) {
            $this->setEmail($fbdata['email']);
        }
        if (isset($fbdata['birthday']) && is_null($this->getBirthdate())) {
            $date = \DateTime::createFromFormat('m/d/Y', $fbdata['birthday']);
            $this->setBirthdate($date);
        }
        if (isset($fbdata['username']) && is_null($this->getFacebookUsername())) {
            $this->setFacebookUsername($fbdata['username']);
        }
    }

    public function setCpf($cpf)
    {
        $cpf = trim(preg_replace('/[^0-9]/', '', $cpf));

        if ($cpf === '') {
            $cpf = null;
        }

        $this->cpf = $cpf;

        return $this;
    }

    public function getCpf()
    {
        return $this->cpf;
    }

    public function setCpfExpiration($cpfExpiration)
    {
        $this->cpfExpiration = $cpfExpiration;

        return $this;
    }

    public function getCpfExpiration()
    {
        return $this->cpfExpiration;
    }

    /**
     * @param \LoginCidadao\CoreBundle\Entity\City $city
     * @return City
     */
    public function setCity(\LoginCidadao\CoreBundle\Entity\City $city = null)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return \LoginCidadao\CoreBundle\Entity\City
     */
    public function getCity()
    {
        return $this->city;
    }

    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        if (!($this->getCreatedAt() instanceof \DateTime)) {
            $this->createdAt = new \DateTime();
        }
        if (!$this->updatedAt) {
            $this->updatedAt = new \DateTime();
        }
    }

    public function setEmailConfirmedAt(\DateTime $emailConfirmedAt = null)
    {
        $this->emailConfirmedAt = $emailConfirmedAt;

        return $this;
    }

    public function getEmailConfirmedAt()
    {
        return $this->emailConfirmedAt;
    }

    public function getSocialNetworksPicture()
    {
        if (!is_null($this->getFacebookId())) {
            return "https://graph.facebook.com/{$this->getFacebookId()}/picture?height=245&width=245";
        }

        return null;
    }

    public function getNotifications()
    {
        return $this->notifications;
    }

    public function getClients()
    {
        return $this->clients;
    }

    public function setClients($var)
    {
        return $this->clients = $var;
    }

    public function checkEmailPending()
    {
        $confirmToken  = $this->getConfirmationToken();
        $notifications = $this->getNotifications();

        if (is_null($confirmToken)) {
            foreach ($notifications as $notification) {
                if ($notification->getTitle() === 'notification.unconfirmed.email.title') {
                    $notification->setRead(true);
                }
            }
        }
    }

    public function setEmailExpiration($emailExpiration)
    {
        $this->emailExpiration = $emailExpiration;

        return $this;
    }

    public function getEmailExpiration()
    {
        return $this->emailExpiration;
    }

    public function setConfirmationToken($confirmationToken)
    {
        parent::setConfirmationToken($confirmationToken);
        $this->setEmailConfirmedAt(null);
    }

    public function setFacebookUsername($facebookUsername)
    {
        $this->facebookUsername = $facebookUsername;

        return $this;
    }

    public function getFacebookUsername()
    {
        return $this->facebookUsername;
    }

    public function getFacebookAccessToken()
    {
        return $this->facebookAccessToken;
    }

    public function setFacebookAccessToken($facebookAccessToken)
    {
        $this->facebookAccessToken = $facebookAccessToken;
        return $this;
    }

    public function setPreviousValidEmail($previousValidEmail)
    {
        $this->previousValidEmail = $previousValidEmail;

        return $this;
    }

    public function getPreviousValidEmail()
    {
        return $this->previousValidEmail;
    }

    public function isCpfExpired()
    {
        return ($this->getCpfExpiration() instanceof \DateTime && $this->getCpfExpiration()
            <= new \DateTime());
    }

    public function hasPassword()
    {
        $password = $this->getPassword();
        return strlen($password) > 0;
    }

    public function setState(State $state = null)
    {
        $this->state = $state;
        return $this;
    }

    public function getState()
    {
        return $this->state;
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
     * @return File
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param string $imageName
     */
    public function setImageName($imageName)
    {
        $this->imageName = $imageName;
    }

    /**
     * @return string
     */
    public function getImageName()
    {
        return $this->imageName;
    }

    public function setProfilePictureUrl($profilePictureUrl)
    {
        $this->profilePictureUrl = $profilePictureUrl;

        return $this;
    }

    public function getProfilePictureUrl()
    {
        return $this->profilePictureUrl;
    }

    /**
     * @JMS\Groups({"public_profile"})
     * @JMS\VirtualProperty
     * @JMS\SerializedName("age_range")
     * @JMS\Type("array")
     * @return array
     */
    public function getAgeRange()
    {
        $today = new \DateTime('today');
        if (!$this->getBirthdate()) {
            return array();
        }
        $age = $this->getBirthdate()->diff($today)->y;

        $range = array();
        if ($age < 13) {
            $range['max'] = 13;
        }
        if ($age >= 13 && $age < 18) {
            $range['min'] = 13;
            $range['max'] = 17;
        }
        if ($age >= 18 && $age < 21) {
            $range['min'] = 18;
            $range['max'] = 20;
        }
        if ($age >= 21) {
            $range['min'] = 21;
        }

        return $range;
    }

    public function hasLocalProfilePicture()
    {
        return !is_null($this->getImageName());
    }

    public function getSuggestions()
    {
        return $this->suggestions;
    }

    public function setSuggestions($suggestions)
    {
        $this->suggestions = $suggestions;

        return $this;
    }

    public function prepareAPISerialize($imageHelper, $templateHelper, $isDev,
                                        $request)
    {
        // User's profile picture
        if ($this->hasLocalProfilePicture()) {
            $picturePath = $imageHelper->asset($this, 'image');
            $pictureUrl  = $request->getUriForPath($picturePath);
            if ($isDev) {
                $pictureUrl = str_replace('/app_dev.php', '', $pictureUrl);
            }
        } else {
            $pictureUrl = $this->getSocialNetworksPicture();
        }
        if (is_null($pictureUrl)) {
            // TODO: fix this and make it comply to DRY
            $picturePath = $templateHelper->getUrl('bundles/logincidadaocore/images/userav.png');
            $pictureUrl  = $request->getUriForPath($picturePath);
            if ($isDev) {
                $pictureUrl = str_replace('/app_dev.php', '', $pictureUrl);
            }
        }
        $this->setProfilePictureUrl($pictureUrl);
        $this->serialize();
    }

    public function isClientAuthorized($app_id)
    {
        foreach ($this->getAuthorizations() as $auth) {
            if ($auth->getClient()->getPublicId() === $app_id) {
                return true;
            }
        }
        return false;
    }

    /**
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

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setGoogleId($var)
    {
        $this->googleId = $var;

        return $this;
    }

    public function getGoogleId()
    {
        return $this->googleId;
    }

    public function setGoogleUsername($var)
    {
        $this->googleUsername = $var;

        return $this;
    }

    public function getGoogleUsername()
    {
        return $this->googleUsername;
    }

    public function setGoogleAccessToken($var)
    {
        $this->googleAccessToken = $var;

        return $this;
    }

    public function getGoogleAccessToken()
    {
        return $this->googleAccessToken;
    }

    public function setCountry(Country $country = null)
    {
        $this->country = $country;
        return $this;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function setComplement($var)
    {
        $this->complement = $var;
        return $this;
    }

    public function getComplement()
    {
        return $this->complement;
    }

    public function getIdCards()
    {
        return $this->idCards;
    }

    public function getBadges()
    {
        return $this->badges;
    }

    public function mergeBadges(array $badges)
    {
        $this->badges = array_merge($this->badges, $badges);
        return $this;
    }

    public function getFullNameOrUsername()
    {
        if (null === $this->firstName) {
            return $this->username;
        }
        return $this->getFullName();
    }

    public function getLogoutKeys()
    {
        return $this->logoutKeys;
    }

    /**
     * @return ArrayCollection
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    public function setLogoutKeys($logoutKeys)
    {
        $this->logoutKeys = $logoutKeys;
        return $this;
    }

    public function setAddresses($addresses)
    {
        $this->addresses = $addresses;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getNotificationOptions()
    {
        return $this->notificationOptions;
    }

    public function setNotificationOptions(ArrayCollection $notificationOptions)
    {
        $this->notificationOptions = $notificationOptions;
        return $this;
    }

    /**
     * Checks whether 2FA is enabled.
     *
     * @return boolean
     */
    public function isTwoFactorAuthenticationEnabled()
    {
        return $this->googleAuthenticatorSecret !== null;
    }

    public function getGoogleAuthenticatorSecret()
    {
        return $this->googleAuthenticatorSecret;
    }

    public function setGoogleAuthenticatorSecret($googleAuthenticatorSecret)
    {
        $this->googleAuthenticatorSecret = $googleAuthenticatorSecret;
        return $this;
    }

    public function getBackupCodes()
    {
        return $this->backupCodes;
    }

    public function setBackupCodes(ArrayCollection $backupCodes)
    {
        $this->backupCodes = $backupCodes;
        return $this;
    }

    public function invalidateBackupCode($code)
    {
        $backupCode = $this->findBackupCode($code);
        $backupCode->setUsed(true);

        return $this;
    }

    public function isBackupCode($code)
    {
        $backupCode = $this->findBackupCode($code);
        return $backupCode !== false && $backupCode->getUsed() === false;
    }

    /**
     * @param string $code
     * @return BackupCode
     */
    private function findBackupCode($code)
    {
        $backupCodes = $this->getBackupCodes();
        foreach ($backupCodes as $backupCode) {
            if ($backupCode->getCode() === $code) {
                return $backupCode;
            }
        }
        return false;
    }

    public function setNationality($var)
    {
        $this->nationality = $var;
        return $this;
    }

    public function getNationality()
    {
        return $this->nationality;
    }

    public function getPlaceOfBirth()
    {
        $location = new SelectData();
        $location->getFromObject($this);
        return $location;
    }

    public function setPlaceOfBirth(SelectData $location)
    {
        $location->toObject($this);
    }

    public function waitUpdate(EntityManager $em, \DateTime $updatedAt)
    {
        $id            = $this->getId();
        $lastUpdatedAt = null;
        $callback      = $this->getCheckUpdateCallback($em, $id, $updatedAt,
            $lastUpdatedAt);
        return LongPollingUtils::runTimeLimited($callback);
    }

    private function getCheckUpdateCallback(EntityManager $em, $id, $updatedAt,
                                            $lastUpdatedAt)
    {
        $people = $em->getRepository('LoginCidadaoCoreBundle:Person');
        return function() use ($id, $people, $em, $updatedAt, $lastUpdatedAt) {
            $em->clear();
            $person = $people->find($id);
            if (!$person->getUpdatedAt()) {
                return false;
            }

            if ($person->getUpdatedAt() > $updatedAt) {
                return $person;
            }

            if ($lastUpdatedAt === null) {
                $lastUpdatedAt = $person->getUpdatedAt();
            } elseif ($person->getUpdatedAt() != $lastUpdatedAt) {
                return $person;
            }

            return false;
        };
    }

    /**
     * @JMS\Groups({"public_profile"})
     * @JMS\VirtualProperty
     * @JMS\SerializedName("given_name")
     */
    public function getGivenName()
    {
        return $this->getFirstName();
    }

    /**
     * @JMS\Groups({"full_name"})
     * @JMS\VirtualProperty
     * @JMS\SerializedName("family_name")
     */
    public function getFamilyName()
    {
        return $this->getSurname();
    }

    /**
     * @JMS\Groups({"mobile", "phone_number"})
     * @JMS\VirtualProperty
     * @JMS\SerializedName("phone_number")
     */
    public function getPhoneNumber()
    {
        return $this->getMobile();
    }

    /**
     * @JMS\Groups({"mobile", "phone_number"})
     * @JMS\VirtualProperty
     * @JMS\SerializedName("phone_number_verified")
     */
    public function getPhoneNumberVerified()
    {
        return false;
    }

    public function getPasswordEncoderName()
    {
        return $this->passwordEncoderName;
    }

    public function setPasswordEncoderName($passwordEncoderName)
    {
        $this->passwordEncoderName = $passwordEncoderName;
        return $this;
    }

    public function getEncoderName()
    {
        $encoder = $this->passwordEncoderName;

        // BC for PR #357
        if ($encoder === null || strlen($encoder) < 1) {
            return null;
        }

        return $encoder;
    }

    public function getShortDisplayName()
    {
        if ($this->getGivenName()) {
            return $this->getGivenName();
        } else {
            return $this->getEmail();
        }
    }
}
