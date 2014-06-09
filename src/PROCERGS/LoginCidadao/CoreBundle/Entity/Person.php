<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use PROCERGS\OAuthBundle\Entity\Client;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\VirtualProperty;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use PROCERGS\Generic\ValidationBundle\Validator\Constraints as PROCERGSAssert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @ORM\Entity(repositoryClass="PROCERGS\LoginCidadao\CoreBundle\Entity\PersonRepository")
 * @UniqueEntity("cpf")
 * @UniqueEntity("username")
 * @ORM\HasLifecycleCallbacks
 * @ExclusionPolicy("all")
 * @Vich\Uploadable
 */
class Person extends BaseUser
{

    /**
     * @Expose
     * @Groups({"id","public_profile"})
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Expose
     * @Groups({"first_name","full_name","public_profile"})
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank(message="Please enter your name.", groups={"Profile"})
     * @Assert\Length(
     *     min=3,
     *     max="255",
     *     minMessage="The name is too short.",
     *     maxMessage="The name is too long.",
     *     groups={"Registration", "Profile"}
     * )
     */
    protected $firstName;

    /**
     * @Expose
     * @Groups({"last_name","full_name"})
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank(message="Please enter your surname.", groups={"Profile"})
     * @Assert\Length(
     *     min=1,
     *     max="255",
     *     minMessage="The surname is too short.",
     *     maxMessage="The surname is too long.",
     *     groups={"Registration", "Profile"}
     * )
     */
    protected $surname;

    /**
     * @Expose
     * @Groups({"username","public_profile"})
     * @PROCERGSAssert\Username
     * @Assert\NotBlank
     * @Assert\Length(
     *     min="1",
     *     max="33",
     *     groups={"Registration", "Profile"}
     * )
     */
    protected $username;

    /**
     * @Expose
     * @Groups({"cpf"})
     * @ORM\Column(type="string", nullable=true, unique=true)
     * @PROCERGSAssert\CPF
     */
    protected $cpf;

    /**
     * @Expose
     * @Groups({"email"})
     */
    protected $email;

    /**
     * @Expose
     * @Groups({"birthdate"})
     * @ORM\Column(type="date", nullable=true)
     */
    protected $birthdate;

    /**
     * @ORM\Column(name="cpf_expiration", type="date", nullable=true)
     */
    protected $cpfExpiration;

    /**
     * @ORM\Column(name="email_expiration", type="datetime", nullable=true)
     */
    protected $emailExpiration;

    /**
     * @Expose
     * @Groups({"cep"})
     * @ORM\Column(type="string", nullable=true)
     * @PROCERGSAssert\CEP
     */
    protected $cep;

    /**
     * @Expose
     * @Groups({"mobile"})
     * @ORM\Column(type="string", nullable=true)
     */
    protected $mobile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     */
    protected $twitterPicture;

    /**
     * @Expose
     * @Groups({"city"})
     * @ORM\ManyToOne(targetEntity="PROCERGS\LoginCidadao\CoreBundle\Entity\City")
     * @ORM\JoinColumn(name="city_id", referencedColumnName="id")
     */
    protected $city;

    /**
     * @var string
     *
     * @ORM\Column(name="facebookId", type="string", length=255, nullable=true, unique=true)
     */
    protected $facebookId;

    /**
     * @var string
     *
     * @ORM\Column(name="facebookUsername", type="string", length=255, nullable=true)
     */
    protected $facebookUsername;

    /**
     * @var string
     *
     * @ORM\Column(name="twitterId", type="string", length=255, nullable=true, unique=true)
     */
    protected $twitterId;

    /**
     * @var string
     *
     * @ORM\Column(name="twitterUsername", type="string", length=255, nullable=true)
     */
    protected $twitterUsername;

    /**
     * @var string
     *
     * @ORM\Column(name="twitterAccessToken", type="string", length=255, nullable=true)
     */
    protected $twitterAccessToken;

    /**
     * @ORM\OneToMany(targetEntity="Authorization", mappedBy="person", cascade={"remove"}, orphanRemoval=true)
     */
    protected $authorizations;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    protected $emailConfirmedAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     */
    protected $previousValidEmail;

    /**
     * @ORM\OneToMany(targetEntity="Notification", mappedBy="person")
     */
    protected $notifications;

    /**
     * @ORM\OneToMany(targetEntity="ClientSuggestion", mappedBy="person")
     */
    protected $suggestions;

    /**
     * @ORM\Column(name="adress", type="string", length=255, nullable=true)
     * @var string
     */
    protected $adress;

    /**
     * @ORM\Column(name="adress_number",type="integer", nullable=true)
     * @var string
     */
    protected $adressNumber;

    /**
     * @ORM\ManyToOne(targetEntity="PROCERGS\LoginCidadao\CoreBundle\Entity\Uf")
     * @ORM\JoinColumn(name="uf_id", referencedColumnName="id")
     */
    protected $uf;

    /**
     * @ORM\Column(name="nfg_access_token", type="string", length=255, nullable=true, unique=true)
     */
    protected $nfgAccessToken;

    /**
     * @Expose
     * @Groups({"nfgprofile"})
     * @ORM\ManyToOne(targetEntity="PROCERGS\LoginCidadao\CoreBundle\Entity\NfgProfile")
     * @ORM\JoinColumn(name="nfg_profile_id", referencedColumnName="id")
     */
    protected $nfgProfile;

    /**
     * @Expose
     * @Groups({"voter_registration"})
     * @ORM\Column(name="voter_registration", type="string", length=12, nullable=true, unique=true)
     * @PROCERGSAssert\VoterRegistration
     */
    protected $voterRegistration;

    /**
     * @Assert\File(
     *      maxSize="2M",
     *      maxSizeMessage="The maxmimum allowed file size is 2MB.",
     *      mimeTypes={"image/png", "image/jpeg", "image/pjpeg"},
     *      mimeTypesMessage="Only JPEG and PNG images are allowed."
     * )
     * @Vich\UploadableField(mapping="user_image", fileNameProperty="imageName")
     * @var File $image
     */
    protected $image;

    /**
     * @ORM\Column(type="string", length=255, name="image_name", nullable=true)
     *
     * @var string $imageName
     */
    protected $imageName;

    /**
     * @Expose
     * @Groups({"picture","public_profile"})
     */
    protected $profilePicutreUrl;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Expose
     * @Groups({"updated_at","public_profile"})
     * @var \DateTime $updatedAt
     */
    protected $updatedAt;
    
    /**
     * @var string
     *
     * @ORM\Column(name="googleId", type="string", length=255, nullable=true, unique=true)
     */
    protected $googleId;
    
    /**
     * @var string
     *
     * @ORM\Column(name="googleUsername", type="string", length=255, nullable=true)
     */
    protected $googleUsername;
    
    /**
     * @var string
     *
     * @ORM\Column(name="googleAccessToken", type="string", length=255, nullable=true)
     */
    protected $googleAccessToken;
    

    public function __construct()
    {
        parent::__construct();
        $this->authorizations = new \Doctrine\Common\Collections\ArrayCollection();
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

    public function getCep()
    {
        return $this->cep;
    }

    public function setCep($cep)
    {
        $cep = preg_replace('/[^0-9]/', '', $cep);
        $this->cep = $cep;
    }

    public function getMobile()
    {
        return $this->mobile;
    }

    public function setMobile($mobile)
    {
        $mobile = preg_replace('/[^0-9]/', '', $mobile);
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

    public function getAuthorizations()
    {
        return $this->authorizations;
    }

    /**
     * Checks if a given Client can access this Person's specified scope.
     * @param \PROCERGS\OAuthBundle\Entity\Client $client
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
     * Checks if this Person has any authorization for a given Client.
     * WARNING: Note that it does NOT validate scope!
     * @param \PROCERGS\OAuthBundle\Entity\Client $client
     */
    public function hasAuthorization(Client $client)
    {
        $authorizations = $this->getAuthorizations();
        foreach ($authorizations as $auth) {
            $c = $auth->getClient();
            if ($c->getId() == $client->getId()) {
                return true;
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
     * @Groups({"full_name"})
     * @VirtualProperty
     * @SerializedName("full_name")
     * @return string
     */
    public function getFullName()
    {
        return $this->getFirstname() . ' ' . $this->getSurname();
    }

    /**
     * @Groups({"badges", "public_profile"})
     * @VirtualProperty
     * @SerializedName("badges")
     * @return array
     */
    public function getDataValid()
    {
        $terms['cpf'] = (is_numeric($this->cpf) && strlen($this->nfgAccessToken));
        $terms['email'] = is_null($this->getConfirmationToken());
        if ($this->getNfgProfile()) {
            $terms['nfg_access_lvl'] = $this->getNfgProfile()->getAccessLvl();
            $terms['voter_registration'] = $this->getNfgProfile()->getVoterRegistrationSit();
        } else {
            $terms['nfg_access_lvl'] = 0;
            $terms['voter_registration'] = false;
        }
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
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
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
     * @param \PROCERGS\LoginCidadao\CoreBundle\Entity\City $city
     * @return City
     */
    public function setCity(\PROCERGS\LoginCidadao\CoreBundle\Entity\City $city = null)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return \PROCERGS\LoginCidadao\CoreBundle\Entity\City
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

    public function checkEmailPending()
    {
        $confirmToken = $this->getConfirmationToken();
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
        return ($this->getCpfExpiration() instanceof \DateTime && $this->getCpfExpiration() <= new \DateTime());
    }

    public function hasPassword()
    {
        $password = $this->getPassword();
        return strlen($password) > 0;
    }

    public function setAdress($var)
    {
        $this->adress = $var;
        return $this;
    }

    public function getAdress()
    {
        return $this->adress;
    }

    public function setAdressNumber($var)
    {
        $this->adressNumber = $var;
        return $this;
    }

    public function getAdressNumber()
    {
        return $this->adressNumber;
    }

    public function setUf($var)
    {
        $this->uf = $var;
        return $this;
    }

    public function getUf()
    {
        return $this->uf;
    }

    public function setNfgAccessToken($var)
    {
        $this->nfgAccessToken = $var;
        return $this;
    }

    public function getNfgAccessToken()
    {
        return $this->nfgAccessToken;
    }

    /**
     *
     * @param \PROCERGS\LoginCidadao\CoreBundle\Entity\NfgProfile $var
     * @return City
     */
    public function setNfgProfile(\PROCERGS\LoginCidadao\CoreBundle\Entity\NfgProfile $var = null)
    {
        $this->nfgProfile = $var;

        return $this;
    }

    /**
     *
     * @return \PROCERGS\LoginCidadao\CoreBundle\Entity\NfgProfile
     */
    public function getNfgProfile()
    {
        return $this->nfgProfile;
    }

    public function setVoterRegistration($var = null)
    {
        if (null === $var) {
            $this->voterRegistration = null;
        } else {
            $this->voterRegistration = preg_replace('/[^0-9]/', '', $var);
        }
        return $this;
    }

    public function getVoterRegistration()
    {
        return $this->voterRegistration;
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
    public function setImage(File $image)
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

    public function setProfilePictureUrl($profilePicutreUrl)
    {
        $this->profilePicutreUrl = $profilePicutreUrl;

        return $this;
    }

    public function getProfilePictureUrl()
    {
        return $this->profilePicutreUrl;
    }

    /**
     * @Groups({"public_profile"})
     * @VirtualProperty
     * @SerializedName("age_range")
     * @Type("array")
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

    public function prepareAPISerialize($imageHelper, $templateHelper, $isDev, $request)
    {
        // User's profile picture
        if ($this->hasLocalProfilePicture()) {
            $picturePath = $imageHelper->asset($this, 'image');
            $pictureUrl = $request->getUriForPath($picturePath);
            if ($isDev) {
                $pictureUrl = str_replace('/app_dev.php', '', $pictureUrl);
            }
        } else {
            $pictureUrl = $this->getSocialNetworksPicture();
        }
        if (is_null($pictureUrl)) {
            // TODO: fix this and make it comply to DRY
            $picturePath = $templateHelper->getUrl('bundles/procergslogincidadaocore/images/userav.png');
            $pictureUrl = $request->getUriForPath($picturePath);
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
    public function setUpdatedAt($var = NULL)
    {
        if ($var === null) {
            $this->updatedAt = new \DateTime();
        } else {
            $this->updatedAt = $var;
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
    
    
}
