<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Security\User\Provider;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseClass;
use Symfony\Component\Security\Core\User\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;

class FOSUBUserProvider extends BaseClass
{

    protected $proxySettings;

    /**
     * Constructor.
     *
     * @param UserManagerInterface $userManager FOSUB user provider.
     * @param array                $properties  Property mapping.
     * @param array                $proxySettings
     */
    public function __construct(UserManagerInterface $userManager,
                                array $properties,
                                array $proxySettings = null)
    {
        $this->userManager = $userManager;
        $this->properties = $properties;
        $this->proxySettings = $proxySettings;
    }

    /**
     * {@inheritDoc}
     */
    public function connect(UserInterface $user, UserResponseInterface $response)
    {
        $property = $this->getProperty($response);
        $username = $response->getUsername();

        $service = $response->getResourceOwner()->getName();

        $setter = 'set' . ucfirst($service);
        $setter_id = $setter . 'Id';
        $setter_token = $setter . 'AccessToken';
        $setter_username = $setter . 'Username';

        if (null !== $previousUser = $this->userManager->findUserBy(array($property => $username))) {
            $previousUser->$setter_id(null);
            $previousUser->$setter_token(null);
            $this->userManager->updateUser($previousUser);
        }

        $screenName = $response->getNickname();
        $user->$setter_id($username);
        $user->$setter_token($response->getAccessToken());
        $user->$setter_username($screenName);

        $this->userManager->updateUser($user);
    }

    /**
     * {@inheritDoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $rawResponse = $response->getResponse();

        $username = $response->getUsername();
        $screenName = $response->getNickname();
        $user = $this->userManager->findUserBy(array($this->getProperty($response) => $username));


        $service = $response->getResourceOwner()->getName();
        $setter = 'set' . ucfirst($service);
        $setter_id = $setter . 'Id';
        $setter_token = $setter . 'AccessToken';
        $setter_username = $setter . 'Username';

        if (null === $user) {
            $user = $this->userManager->createUser();
            $user->$setter_id($username);
            $user->$setter_token($response->getAccessToken());
            $user->$setter_username($screenName);

            $fullName = explode(' ', $response->getRealName(), 2);
            $timestamp = microtime();

            $user->setFirstName($fullName[0]);
            $user->setSurname($fullName[1]);

            $defaultUsername = "$screenName@$service";
            $availableUsername = $this->userManager->getNextAvailableUsername($screenName,
                    10, $defaultUsername);
            $user->setUsername($availableUsername);
            $user->setEmail("$screenName@{$service}_$timestamp");
            $user->setPassword('');
            $user->setEnabled(true);

            if ($service === 'twitter') {
                $user->updateTwitterPicture($rawResponse, $this->proxySettings);
            }

            $this->userManager->updateUser($user);

            return $user;
        } else {
            if ($service === 'twitter') {
                $user->updateTwitterPicture($rawResponse, $this->proxySettings);
                $this->userManager->updateUser($user);
            }
        }

        $user = parent::loadUserByOAuthUserResponse($response);

        $serviceName = $response->getResourceOwner()->getName();
        $setter = 'set' . ucfirst($serviceName) . 'AccessToken';

        $user->$setter($response->getAccessToken());

        return $user;
    }

}
