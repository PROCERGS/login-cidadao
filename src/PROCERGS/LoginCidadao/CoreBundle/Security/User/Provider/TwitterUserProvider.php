<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Security\User\Provider;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use PROCERGS\Generic\TwitterOAuthProxiedBundle\Service\TwitterOAuth;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use FOS\UserBundle\Model\UserManager;
use Symfony\Component\Validator\Validator;
use Symfony\Component\HttpFoundation\Session\Session;

class TwitterUserProvider implements UserProviderInterface
{

    protected $twitter_oauth;
    protected $userManager;
    protected $validator;
    protected $session;

    public function __construct(TwitterOAuth $twitter_oauth, UserManager $userManager, Validator $validator, Session $session)
    {
        $this->twitter_oauth = $twitter_oauth;
        $this->userManager = $userManager;
        $this->validator = $validator;
        $this->session = $session;
    }

    public function supportsClass($class)
    {
        return $this->userManager->supportsClass($class);
    }

    public function findUserByTwitterId($twitterId)
    {
        return $this->userManager->findUserBy(compact('twitterId'));
    }

    public function loadUserByUsername($username)
    {
        $user = $this->findUserByTwitterId($username);

        $this->twitter_oauth->setOAuthToken($this->session->get('access_token'), $this->session->get('access_token_secret'));

        try {
            $info = $this->twitter_oauth->get('account/verify_credentials');
        } catch (Exception $e) {
            $info = null;
        }

        if (!empty($info)) {
            if (empty($user)) {
                $user = $this->userManager->createUser();
                $user->setEnabled(true);
                $user->setPassword('');
                $user->setAlgorithm('');
            }
var_dump($info); die();
            $username = $info->screen_name;

            $user->setTwitterId($info->id);
            $user->setTwitterUsername($username);
            $user->setEmail('');
            $user->setFirstName($info->name);

            $this->userManager->updateUser($user);
        }

        if (empty($user)) {
            throw new UsernameNotFoundException('The user is not authenticated on twitter.');
        }

        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$this->supportsClass(get_class($user)) || !$user->getTwitterId()) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getTwitterId());
    }

}
