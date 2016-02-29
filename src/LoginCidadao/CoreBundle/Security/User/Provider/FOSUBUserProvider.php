<?php

namespace LoginCidadao\CoreBundle\Security\User\Provider;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseClass;
use Symfony\Component\Security\Core\User\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use LoginCidadao\CoreBundle\Security\Exception\AlreadyLinkedAccount;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use LoginCidadao\CoreBundle\Security\Exception\MissingEmailException;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Form\Factory\FactoryInterface;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use LoginCidadao\ValidationBundle\Validator\Constraints\UsernameValidator;

class FOSUBUserProvider extends BaseClass
{

    protected $proxySettings;
    protected $session;
    protected $dispatcher;
    protected $container;
    protected $formFactory;

    /**
     * Constructor.
     *
     * @param UserManagerInterface $userManager FOSUB user provider.
     * @param array                $properties  Property mapping.
     * @param array                $proxySettings
     */
    public function __construct(UserManagerInterface $userManager,
                                SessionInterface $session,
                                EventDispatcherInterface $dispatcher,
                                ContainerInterface $container,
                                FactoryInterface $formFactory,
                                array $properties, array $proxySettings = null)
    {
        $this->userManager = $userManager;
        $this->session = $session;
        $this->dispatcher = $dispatcher;
        $this->container = $container;
        $this->formFactory = $formFactory;
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

        $setter = 'set'.ucfirst($service);
        $setter_id = $setter.'Id';
        $setter_token = $setter.'AccessToken';
        $setter_username = $setter.'Username';

        if (null !== $previousUser = $this->userManager->findUserBy(array("{$service}Id" => $username))) {
            throw new AlreadyLinkedAccount();
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


        $service = $response->getResourceOwner()->getName();
        $setter = 'set'.ucfirst($service);
        $setter_id = $setter.'Id';
        $setter_token = $setter.'AccessToken';
        $setter_username = $setter.'Username';

        $newUser = false;
        $user = $this->userManager->findUserBy(array("{$service}Id" => $username));

        if (null === $user) {
            switch ($service) {
                case 'twitter':
                    $email = $this->session->get('twitter.email');
                    if (!$email) {
                        throw new MissingEmailException();
                    } else {
                        $this->session->remove('twitter.email');
                    }
                    $defaultUsername = "$screenName@$service";
                break;
                case 'google':
                    $email = $rawResponse['email'];
                    $defaultUsername = $email;
                    break;
                default:
                    $email = $rawResponse['email'];
                    $defaultUsername = $email;
                break;
            }
            $newUser = true;
            $user = $this->userManager->createUser();
            $user->$setter_id($username);
            $user->$setter_token($response->getAccessToken());
            $user->$setter_username($screenName);

            $fullName = explode(' ', $response->getRealName(), 2);
            if (isset($fullName[0][1]) && $fullName[0][1] != '') {
                $user->setFirstName($fullName[0]);
            }
            if (isset($fullName[1][1]) && $fullName[1][1] != '') {
                $user->setSurname($fullName[1]);
            }
            
            if (!UsernameValidator::isUsernameValid($screenName)) {
                $screenName = UsernameValidator::getValidUsername();
            }
            $availableUsername = $this->userManager->getNextAvailableUsername($screenName,
                    10, $defaultUsername);
            $user->setUsername($availableUsername);
            $user->setEmail($email);
            $user->setPassword('');
            $user->setEnabled(true);
            $this->userManager->updateCanonicalFields($user);

            $form = $this->formFactory->createForm();
            $form->setData($user);

            $request = $this->container->get('request');
            $eventResponse = new \Symfony\Component\HttpFoundation\RedirectResponse('/');
            $event = new FormEvent($form, $request);
            if ($newUser) {
                $this->dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS,
                        $event);
            }

            $this->userManager->updateUser($user);

            if ($newUser) {
                $this->dispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED,
                        new FilterUserResponseEvent($user, $request,
                        $eventResponse));
            }

            return $user;
        }

        $user = parent::loadUserByOAuthUserResponse($response);

        $serviceName = $response->getResourceOwner()->getName();
        $setter = 'set'.ucfirst($serviceName).'AccessToken';

        $user->$setter($response->getAccessToken());

        return $user;
    }

}
