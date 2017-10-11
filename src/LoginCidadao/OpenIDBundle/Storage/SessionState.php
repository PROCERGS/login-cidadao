<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Storage;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SessionState
{
    /** @var EntityManager */
    protected $em;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    public function __construct(EntityManager $em,
                                TokenStorageInterface $tokenStorage)
    {
        $this->em           = $em;
        $this->tokenStorage = $tokenStorage;
    }

    public function getSessionState($client_id, $sessionId)
    {
        $client = $this->getClient($client_id);

        $url  = $client->getMetadata()->getClientUri();
        $salt = bin2hex(random_bytes(15));

        $state = $client_id.$url.$sessionId.$salt;

        return hash('sha256', $state).".$salt";
    }

    public function getSessionId()
    {
        $token = $this->tokenStorage->getToken();
        if ($token !== null) {
            return hash('sha256', $token->serialize());
        } else {
            return '';
        }
    }

    /**
     * @param string $client_id
     * @return \LoginCidadao\OAuthBundle\Entity\Client
     */
    private function getClient($client_id)
    {
        $id = explode('_', $client_id);
        return $this->em->getRepository('LoginCidadaoOAuthBundle:Client')->find($id[0]);
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        $token = $this->tokenStorage->getToken();
        if ($token !== null) {
            $state  = hash('sha256', $token->serialize());
            $cookie = new Cookie('session_state', $state, 0, '/', null, false,
                false);
            $event->getResponse()->headers->setCookie($cookie);
        } else {
            $event->getResponse()->headers->removeCookie('session_state');
        }
    }
}
