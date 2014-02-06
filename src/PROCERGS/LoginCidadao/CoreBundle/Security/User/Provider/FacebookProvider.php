<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Security\User\Provider;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Facebook;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use \BaseFacebook;
use \FacebookApiException;

class FacebookProvider implements UserProviderInterface
{

    /**
     * @var \Facebook
     */
    protected $facebook;
    protected $userManager;
    protected $validator;
    protected $container;

    public function __construct(BaseFacebook $facebook, $userManager, $validator, $container)
    {
        $this->facebook = $facebook;

        // Add this to not have the error "the ssl certificate is invalid."
        Facebook::$CURL_OPTS[CURLOPT_SSL_VERIFYPEER] = false;
        Facebook::$CURL_OPTS[CURLOPT_SSL_VERIFYHOST] = 2;

        // Proxy Settings
        $proxy = $container->getParameter('http_proxy');
        Facebook::$CURL_OPTS[CURLOPT_PROXYTYPE] = 'HTTP';
        Facebook::$CURL_OPTS[CURLOPT_PROXY] = 'HTTP';
        Facebook::$CURL_OPTS[CURLOPT_PROXYPORT] = 'HTTP';
        Facebook::$CURL_OPTS[CURLOPT_PROXYUSERPWD] = 'HTTP';

        $this->userManager = $userManager;
        $this->validator = $validator;
        $this->container = $container;
    }

}
