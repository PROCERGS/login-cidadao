<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use PROCERGS\OAuthBundle\Entity\Client;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use PROCERGS\Generic\ValidationBundle\Validator\Constraints as PROCERGSAssert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Entity(repositoryClass="PROCERGS\LoginCidadao\CoreBundle\Entity\PersonRepository")
 * @UniqueEntity("cpf")
 * @UniqueEntity("username")
 * @ORM\HasLifecycleCallbacks
 * @ExclusionPolicy("all")
 */
class Person extends BaseUser
{

    /**
     * @Expose
     * @Groups({"id"})
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Expose
     * @Groups({"name"})
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
     * @Groups({"name"})
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
     * @Groups({"full_name"})
     * @var string
     */
    protected $fullName;

    /**
     * @Expose
     * @Groups({"username"})
     * @PROCERGSAssert\Username
     * @Assert\NotBlank
     * @Assert\Length(min="1", max="33")
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
     */
    protected $picturePath;
    protected $tempPicturePath;

    /**
     * @Assert\Image(maxSize="2M",mimeTypes={"image/jpeg", "image/png"}, maxSizeMessage="The maxmimum allowed file size is 2MB.",mimeTypesMessage="Only the jpeg and png filetypes are allowed.")
     */
    protected $pictureFile;

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
     * @ORM\Column(name="cpf_nfg", type="datetime", nullable=true)
     */
    protected $cpfNfg;

    /**
     * @ORM\OneToMany(targetEntity="Notification", mappedBy="person")
     */
    protected $notifications;
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
        $this->fullName = $this->getFullName();
        return serialize(array($this->facebookId, parent::serialize()));
    }

    public function unserialize($data)
    {
        list($this->facebookId, $parentData) = unserialize($data);
        parent::unserialize($parentData);
    }

    /**
     * Get the full name of the user (first + last name)
     * @return string
     */
    public function getFullName()
    {
        return $this->getFirstname() . ' ' . $this->getSurname();
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

    public function setCpfNfg($var)
    {
        $this->cpfNfg = $var;
        return $this;
    }

    public function getCpfNfg()
    {
        return $this->cpfNfg;
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

    public function getAbsolutePicturePath()
    {
        return null === $this->picturePath ? null : $this->getPictureUploadRootDir() . DIRECTORY_SEPARATOR . $this->picturePath;
    }

    public function getPictureWebPath()
    {
        return null === $this->picturePath ? null : $this->getPictureUploadDir() . '/' . $this->picturePath;
    }

    protected function getPictureUploadRootDir()
    {
        return __DIR__ . '/../../../../../web/' . $this->getPictureUploadDir();
    }

    protected function getPictureUploadDir()
    {
        return 'uploads/profile-pictures';
    }

    public function setPictureFile(UploadedFile $pictureFile = null)
    {
        $this->pictureFile = $pictureFile;
        if (isset($this->picturePath)) {
            $this->tempPicturePath = $this->picturePath;
            $this->picturePath = null;
        } else {
            $this->picturePath = 'initial';
        }
    }

    /**
     *
     * @return UploadedFile
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

    public function setTwitterPicture($twitterPicture)
    {
        $this->twitterPicture = $twitterPicture;

        return $this;
    }

    public function getTwitterPicture()
    {
        return $this->twitterPicture;
    }

    public function getSocialNetworksPicture()
    {
        if (!is_null($this->getFacebookId())) {
            return "https://graph.facebook.com/{$this->getFacebookId()}/picture?height=245&width=245";
        }
        if (!is_null($this->getTwitterId())) {
            if (!is_null($this->getTwitterPicture())) {
                return $this->getTwitterPicture();
            }
        }

        return null;
    }

    public function updateTwitterPicture($rawResponse, $proxySettings = null)
    {
        $pictureAddress = $rawResponse['profile_image_url'];
        $currentPicture = $this->getTwitterPicture();

        $context = null;
        if ($currentPicture !== $pictureAddress) {
            if (ini_get('allow_url_fopen')) {
                if (!empty($proxySettings)) {
                    $auth = base64_encode($proxySettings['auth']);
                    $opts = array('http' => array(
                        'proxy' => "tcp://{$proxySettings['host']}:{$proxySettings['port']}",
                        'request_fulluri' => true,
                        'header' => array(
                            "Proxy-Authorization: Basic $auth"
                        )
                    ));
                    $context = stream_context_create($opts);
                }
                $picture = file_get_contents($pictureAddress, false, $context);
            } elseif (function_exists('curl_init')) {                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                if (ini_get('open_basedir')) {
                    //@TODO some gambi
                } else {
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                }
                curl_setopt($ch, CURLOPT_URL, $url);
                if (isset($proxySettings['host'], $proxySettings['port'])) {
                    curl_setopt($ch, CURLOPT_PROXYTYPE, $proxySettings['type']);
                    curl_setopt($ch, CURLOPT_PROXY, $proxySettings['host']);
                    curl_setopt($ch, CURLOPT_PROXYPORT, $proxySettings['port']);
                    if (isset($proxySettings['auth'])) {
                        curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxySettings['auth']);
                    }
                }
                $picture = curl_exec($ch);
                curl_close($ch);
                $ch = null;
            } else {
                throw new \Exception('No way to open sockets');
            }
            $this->setTwitterPicture($pictureAddress);
            $ext = explode('.', $pictureAddress);
            $filename = sha1($this->getId()) . '.' . array_pop($ext);
            $this->picturePath = $filename;
            file_put_contents($this->getAbsolutePicturePath(), $picture);            
        }
    }

    public function hasLocalPicture()
    {
        return file_exists($this->getAbsolutePicturePath());
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
    

}
