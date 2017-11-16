<?php

namespace LoginCidadao\CoreBundle\Model;

use Doctrine\ORM\EntityManagerInterface;
use LoginCidadao\CoreBundle\Entity\BackupCode;
use LoginCidadao\CoreBundle\Tests\LongPolling\LongPollableInterface;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\UserInterface;
use LoginCidadao\CoreBundle\Entity\City;
use LoginCidadao\CoreBundle\Entity\Country;
use LoginCidadao\CoreBundle\Entity\State;
use Symfony\Component\Security\Core\Encoder\EncoderAwareInterface;
use LoginCidadao\OAuthBundle\Entity\Client;
use JMS\Serializer\Annotation as JMS;

interface PersonInterface extends EncoderAwareInterface, UserInterface, LocationAwareInterface, LongPollableInterface, TwoFactorInterface
{
    public function getId();

    public function getEmail();

    /**
     * @param string $email
     * @return self
     */
    public function setEmail($email);

    public function getFirstName();

    /**
     * @param $firstName
     * @return self
     */
    public function setFirstName($firstName);

    public function getSurname();

    /**
     * @param $suname
     * @return self
     */
    public function setSurname($suname);

    /**
     * @return \DateTime
     */
    public function getBirthdate();

    /**
     * @param $birthdate
     * @return self
     */
    public function setBirthdate($birthdate);

    public function getMobile();

    /**
     * @param $mobile
     * @return self
     */
    public function setMobile($mobile);

    public function getAuthorizations();

    /**
     * Checks if a given Client can access this Person's specified scope.
     * @param \LoginCidadao\OAuthBundle\Entity\Client $client
     * @param mixed $scope can be a single scope or an array with several.
     * @return boolean
     */
    public function isAuthorizedClient(Client $client, $scope);

    /**
     * Checks if this Person has any authorization for a given Client.
     * WARNING: Note that it does NOT validate scope!
     * @param \LoginCidadao\OAuthBundle\Entity\Client | integer $client
     */
    public function hasAuthorization($client);

    public function setFacebookId($facebookId);

    public function getFacebookId();

    public function setTwitterId($twitterId);

    public function getTwitterId();

    public function setTwitterUsername($twitterUsername);

    public function getTwitterUsername();

    public function setTwitterAccessToken($twitterAccessToken);

    public function getTwitterAccessToken();

    /**
     * Get the full name of the user (first + last name)
     * @JMS\Groups({"full_name"})
     * @JMS\VirtualProperty
     * @JMS\SerializedName("full_name")
     * @return string
     */
    public function getFullName();

    /**
     * @param $cpf
     * @return self
     */
    public function setCpf($cpf);

    public function getCpf();

    public function setCreatedAt(\DateTime $createdAt);

    public function getCreatedAt();

    public function setCreatedAtValue();

    public function setEmailConfirmedAt(\DateTime $emailConfirmedAt = null);

    public function getEmailConfirmedAt();

    public function getSocialNetworksPicture();

    public function getClients();

    public function setEmailExpiration($emailExpiration);

    public function getEmailExpiration();

    public function getConfirmationToken();

    public function setConfirmationToken($confirmationToken);

    public function setFacebookUsername($facebookUsername);

    public function getFacebookUsername();

    public function setPreviousValidEmail($previousValidEmail);

    public function getPreviousValidEmail();

    public function hasPassword();

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the  update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile $image
     */
    public function setImage($image);

    /**
     * @return File
     */
    public function getImage();

    /**
     * @param string $imageName
     */
    public function setImageName($imageName);

    /**
     * @return string
     */
    public function getImageName();

    public function setProfilePictureUrl($profilePictureUrl);

    public function getProfilePictureUrl();

    /**
     * @JMS\Groups({"public_profile"})
     * @JMS\VirtualProperty
     * @JMS\SerializedName("age_range")
     * @JMS\Type("array")
     * @return array
     */
    public function getAgeRange();

    public function hasLocalProfilePicture();

    public function getSuggestions();

    public function setSuggestions($suggestions);

    public function prepareAPISerialize(
        $imageHelper,
        $templateHelper,
        $isDev,
        $request
    );

    public function isClientAuthorized($app_id);

    public function setUpdatedAt($var = null);

    public function getUpdatedAt();

    public function setGoogleId($var);

    public function getGoogleId();

    public function setGoogleUsername($var);

    public function getGoogleUsername();

    public function setGoogleAccessToken($var);

    public function getGoogleAccessToken();

    public function setComplement($var);

    public function getComplement();

    /**
     * @return IdCardInterface[]
     */
    public function getIdCards();

    public function getBadges();

    /**
     * Merges badges <code>$badges</code> into a person's badges.
     *
     * @param array $badges
     */
    public function mergeBadges(array $badges);

    public function getGoogleAuthenticatorSecret();

    public function setGoogleAuthenticatorSecret($googleAuthenticatorSecret);

    /**
     * @return City
     */
    public function getCity();

    /**
     * @return State
     */
    public function getState();

    /**
     * @return Country
     */
    Public function getCountry();

    /**
     * @param bool $verified
     * @return $this
     */
    public function setPhoneNumberVerified($verified = false);

    /**
     * @return bool
     */
    public function getPhoneNumberVerified();

    /**
     * @return ArrayCollection
     */
    public function getAddresses();

    /**
     * @return BackupCode[]
     */
    public function getBackupCodes();
}
