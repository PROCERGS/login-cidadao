<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\GrantType;

use OAuth2\OpenID\GrantType\AuthorizationCode as BaseAuthorizationCode;
use OAuth2\ResponseType\AccessTokenInterface;
use LoginCidadao\OpenIDBundle\Storage\SessionState;

class AuthorizationCode extends BaseAuthorizationCode
{
    /** @var SessionState */
    private $sessionStateStorage;

    public function createAccessToken(AccessTokenInterface $accessToken,
                                        $client_id, $user_id, $scope)
    {
        $token = parent::createAccessToken($accessToken, $client_id, $user_id,
                $scope);

        if (array_key_exists('session_state', $token) === false) {
            $sessionState = $this->sessionStateStorage->getSessionState($client_id, $this->authCode['session_id']);

            $token['session_state'] = $sessionState;
        }

        return $token;
    }

    public function setSessionStateStorage(SessionState $sessionStateStorage)
    {
        $this->sessionStateStorage = $sessionStateStorage;
    }
}
