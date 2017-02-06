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


class AccessToken extends \OAuth2\ResponseType\AccessToken
{
    protected function generateAccessToken()
    {
        return hash_hmac('sha512', bin2hex(random_bytes(50)), microtime());
    }
}
