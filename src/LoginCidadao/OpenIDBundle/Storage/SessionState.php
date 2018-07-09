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

use LoginCidadao\OAuthBundle\Model\ClientInterface;
use LoginCidadao\OpenIDBundle\Manager\ClientManager;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SessionState
{
    /** @var ClientManager */
    private $clientManager;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    public function __construct(ClientManager $clientManager, TokenStorageInterface $tokenStorage)
    {
        $this->clientManager = $clientManager;
        $this->tokenStorage = $tokenStorage;
    }

    public function getSessionState($client_id, $sessionId)
    {
        $client = $this->getClient($client_id);

        $url = $client->getMetadata()->getClientUri();
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
     * @return ClientInterface
     */
    private function getClient($client_id)
    {
        return $this->clientManager->getClientById($client_id);
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if ($event->isMasterRequest()) {
            $sessionId = $this->getSessionId();
            if ($sessionId !== '') {
                $cookie = new Cookie('session_state', $sessionId, 0, '/', null, false, false);
                $event->getResponse()->headers->setCookie($cookie);
            } else {
                $event->getResponse()->headers->removeCookie('session_state');
            }
        }
    }
}
