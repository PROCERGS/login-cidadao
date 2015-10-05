<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Security\User\Provider;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Facebook;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use \BaseFacebook;
use \FacebookApiException;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use FOS\UserBundle\Form\Factory\FactoryInterface;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use PROCERGS\LoginCidadao\CoreBundle\Security\Exception\AlreadyLinkedAccount;
use PROCERGS\LoginCidadao\CoreBundle\Exception\LcFcGbException;

class FacebookProvider implements UserProviderInterface
{
    /**
     * @var \Facebook
     */
    protected $facebook;
    protected $userManager;
    protected $validator;
    protected $container;
    protected $dispatcher;
    protected $formFactory;

    public function __construct(BaseFacebook $facebook, $userManager,
                                $validator, $container,
                                EventDispatcherInterface $dispatcher,
                                FactoryInterface $formFactory)
    {
        $this->facebook = $facebook;

        // Add this to not have the error "the ssl certificate is invalid."
        Facebook::$CURL_OPTS[CURLOPT_SSL_VERIFYPEER] = false;
        Facebook::$CURL_OPTS[CURLOPT_SSL_VERIFYHOST] = 2;

        // Proxy Settings
        try {
            $proxy                                     = $container->getParameter('http_proxy');
            Facebook::$CURL_OPTS[CURLOPT_PROXYTYPE]    = $proxy['type'];
            Facebook::$CURL_OPTS[CURLOPT_PROXY]        = $proxy['host'];
            Facebook::$CURL_OPTS[CURLOPT_PROXYPORT]    = $proxy['port'];
            Facebook::$CURL_OPTS[CURLOPT_PROXYUSERPWD] = $proxy['auth'];
        } catch (ParameterNotFoundException $e) {
            // no proxy
        }

        $this->userManager = $userManager;
        $this->validator   = $validator;
        $this->container   = $container;
        $this->dispatcher  = $dispatcher;
        $this->formFactory = $formFactory;
    }

    public function supportsClass($class)
    {
        return $this->userManager->supportsClass($class);
    }

    public function findUserByFbId($fbId)
    {
        return $this->userManager->findUserBy(array('facebookId' => $fbId));
    }

    public function findUserByUsername($username)
    {
        return $this->userManager->findUserBy(array('username' => $username));
    }

    public function connectExistingAccount()
    {

        try {
            $fbdata = $this->facebook->api('/me?fields=id,name,first_name,last_name,email,age_range,birthday');
        } catch (FacebookApiException $e) {
            $fbdata = null;
            return false;
        }

        $alreadyExistingAccount = $this->findUserByFbId($fbdata['id']);

        if (!empty($alreadyExistingAccount)) {
            return false;
        }

        if (!empty($fbdata)) {

            $currentUserObj = $this->container->get('security.context')->getToken()->getUser();

            $user = $this->findUserByUsername($currentUserObj->getUsername());

            if (empty($user)) {
                return false;
            }

            $user->setFBData($fbdata);
            $user->setFacebookAccessToken($this->facebook->getAccessToken());

            if (count($this->validator->validate($user, 'Facebook'))) {
                // TODO: the user was found obviously, but doesnt match our expectations, do something smart
                throw new UsernameNotFoundException('The facebook user could not be stored');
            }
            $this->userManager->updateUser($user);

            return true;
        }

        return false;
    }

    public function loadUserByUsername($username)
    {
        $secToken = $this->container->get('security.context')->getToken();
        if (!is_null($secToken) && !is_null($secToken->getUser())) {
            $currentUserObj = $secToken->getUser();
        } else {
            $currentUserObj = null;
        }

        $newUser = false;
        $user    = $this->findUserByFbId($username);

        if (($user && $currentUserObj)) {
            if ($user->getFacebookId() != $currentUserObj->getFacebookId()) {
                throw new AlreadyLinkedAccount();
            }
        }

        try {
            $fbdata = $this->facebook->api('/me?fields=id,name,first_name,last_name,email,age_range,birthday');
        } catch (FacebookApiException $e) {
            switch ($e->getType()) {
                case 'OAuthException':
                case 'invalid_token':
                case 'Exception':
                    throw new LcFcGbException();
                    break;
            }
            $fbdata = null;
        }
        if (!empty($fbdata)) {
            if (empty($user)) {
                if (!($currentUserObj instanceof UserInterface)) {
                    $newUser = true;
                    $user    = $this->userManager->createUser();
                    $user->setEnabled(true);
                    $user->setPassword('');
                    $user->setEmailConfirmedAt(new \DateTime());
                    $user->setEmailExpiration(null);
                } else {
                    $user = $currentUserObj;
                }
            }

            if ($user->getUsername() == '' || $user->getUsername() == null) {
                $availUsername = $this->prepareFacebookUsername($username,
                    $fbdata);
                $user->setUsername($availUsername);
            }

            $user->setFBData($fbdata);
            $user->setFacebookAccessToken($this->facebook->getAccessToken());

            if (count($this->validator->validate($user, 'Facebook'))) {
                // TODO: the user was found obviously, but doesnt match our expectations, do something smart
                throw new UsernameNotFoundException('The facebook user could not be stored');
            }

            $form = $this->formFactory->createForm();
            $form->setData($user);

            $request       = $this->container->get('request');
            $eventResponse = new \Symfony\Component\HttpFoundation\RedirectResponse('/');
            $event         = new FormEvent($form, $request);
            if ($newUser) {
                $this->dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS,
                    $event);
            }

            $this->userManager->updateUser($user);

            if ($newUser) {
                $this->dispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED,
                    new FilterUserResponseEvent($user, $request, $eventResponse));
            }
        }

        if (empty($user)) {
            // TODO: the user was found obviously, but doesnt match our expectations, do something smart
            throw new UsernameNotFoundException('The facebook user could not be stored');
        }

        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$this->supportsClass(get_class($user)) || !$user->getFacebookId()) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.',
                get_class($user)));
        }

        return $this->loadUserByUsername($user->getFacebookId());
    }

    private function prepareFacebookUsername($username, $fbdata)
    {
        $defaultUsername      = $username.'@facebook.com';
        $fbUsernameComponents = array();
        if (isset($fbdata['first_name'])) {
            $fbUsernameComponents[] = strtolower($fbdata['first_name']);
        }
        if (isset($fbdata['last_name'])) {
            $fbUsernameComponents[] = strtolower($fbdata['last_name']);
        }

        if (count($fbUsernameComponents) > 0) {
            $fbUsername = implode('.', $fbUsernameComponents);
        } else {
            $fbUsername = $username;
        }

        $availUsername = $this->userManager->getNextAvailableUsername((isset($fbdata['username'])
                    ? $fbdata['username'] : $fbUsername), 10, $defaultUsername);

        return $availUsername;
    }
}
