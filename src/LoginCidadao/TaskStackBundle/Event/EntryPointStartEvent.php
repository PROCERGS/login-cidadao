<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\TaskStackBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class EntryPointStartEvent extends Event
{
    /** @var Request */
    private $request;

    /** @var AuthenticationException */
    private $authenticationException;

    public function __construct(Request $request, AuthenticationException $authenticationException = null)
    {
        $this->request = $request;
        $this->authenticationException = $authenticationException;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return AuthenticationException
     */
    public function getAuthenticationException()
    {
        return $this->authenticationException;
    }
}
