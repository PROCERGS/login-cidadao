<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\NfgBundle\Exception;

/**
 * NfgAccountCollisionException is thrown when an user tries to connect
 * its login-cidadao with an NFG account that is already linked to another user.
 *
 * @package PROCERGS\LoginCidadao\NfgBundle\Exception
 */
class NfgAccountCollisionException extends \RuntimeException
{
    /** @var string */
    private $accessToken;

    /**
     * NfgAccountCollisionException constructor.
     * @param string $accessToken
     * @param string $message
     * @param int $code
     * @param \Exception $previous
     */
    public function __construct($accessToken = null, $message = "", $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->accessToken = $accessToken;
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param string $accessToken
     * @return NfgAccountCollisionException
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }
}
