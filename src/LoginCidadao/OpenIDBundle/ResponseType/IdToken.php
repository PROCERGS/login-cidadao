<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\ResponseType;

use OAuth2\OpenID\ResponseType\IdToken as BaseIdToken;
use OAuth2\OpenID\Storage\UserClaimsInterface;
use OAuth2\Storage\PublicKeyInterface;
use OAuth2\Encryption\EncryptionInterface;

class IdToken extends BaseIdToken
{
    /** @var UserClaimsInterface */
    protected $userClaimsStorage;

    /** @var PublicKeyInterface */
    protected $publicKeyStorage;

    /** @var EncryptionInterface */
    protected $encryptionUtil;

    protected function encodeToken(array $token, $client_id = null)
    {
        $private_key = $this->publicKeyStorage->getPrivateKey($client_id);
        $algorithm   = $this->publicKeyStorage->getEncryptionAlgorithm($client_id);

        $token['kid'] = 'pub';

        return $this->encryptionUtil->encode($token, $private_key, $algorithm);
    }
}
