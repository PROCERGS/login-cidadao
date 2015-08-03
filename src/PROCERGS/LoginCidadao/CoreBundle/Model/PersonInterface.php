<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Model;

use PROCERGS\OAuthBundle\Entity\Client;
use JMS\Serializer\Annotation as JMS;
use Doctrine\ORM\EntityManager;

interface PersonInterface
{

    public function getEmail();

    public function setEmail($email);

    public function getFirstName();

    public function setFirstName($firstName);

    public function getSurname();

    public function setSurname($suname);

    public function getBirthdate();

    public function setBirthdate($birthdate);

    public function getMobile();

    public function setMobile($mobile);

    public function getAuthorizations();

    /**
     * Checks if a given Client can access this Person's specified scope.
     * @param \PROCERGS\OAuthBundle\Entity\Client $client
     * @param mixed $scope can be a single scope or an array with several.
     * @return boolean
     */
    public function isAuthorizedClient(Client $client, $scope);

    /**
     * Checks if this Person has any authorization for a given Client.
     * WARNING: Note that it does NOT validate scope!
     * @param \PROCERGS\OAuthBundle\Entity\Client | integer $client
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

    public function setCpf($cpf);

    public function getCpf();

    public function setCpfExpiration($cpfExpiration);

    public function getCpfExpiration();

    public function setCreatedAt(\DateTime $createdAt);

    public function getCreatedAt();

    public function setCreatedAtValue();

    public function setEmailConfirmedAt(\DateTime $emailConfirmedAt = null);

    public function getEmailConfirmedAt();

    public function getSocialNetworksPicture();

    public function getNotifications();

    public function getClients();

    public function checkEmailPending();

    public function setEmailExpiration($emailExpiration);

    public function getEmailExpiration();

    public function setConfirmationToken($confirmationToken);

    public function setFacebookUsername($facebookUsername);

    public function getFacebookUsername();

    public function setPreviousValidEmail($previousValidEmail);

    public function getPreviousValidEmail();

    public function isCpfExpired();

    public function hasPassword();

    public function setNfgAccessToken($var);

    public function getNfgAccessToken();

    /**
     *
     * @return \PROCERGS\LoginCidadao\CoreBundle\Entity\NfgProfile
     */
    public function getNfgProfile();

    public function setVoterRegistration($var = null);

    public function getVoterRegistration();

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

    public function setProfilePictureUrl($profilePicutreUrl);

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

    public function prepareAPISerialize($imageHelper, $templateHelper, $isDev,
                                        $request);

    public function isClientAuthorized($app_id);

    public function setUpdatedAt($var = NULL);

    public function getUpdatedAt();

    public function setGoogleId($var);

    public function getGoogleId();

    public function setGoogleUsername($var);

    public function getGoogleUsername();

    public function setGoogleAccessToken($var);

    public function getGoogleAccessToken();

    public function setComplement($var);

    public function getComplement();

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
     * @param EntityManager $em
     * @param \DateTime $updatedAt
     * @return PersonInterface
     */
    public function waitUpdate(EntityManager $em, \DateTime $updatedAt);
}
