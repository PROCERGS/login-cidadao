<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\EventListener;

use LoginCidadao\CoreBundle\Service\RegisterRequestedScope;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\HttpUtils;

class LoginEntryPoint implements AuthenticationEntryPointInterface
{
    /** @var HttpUtils */
    private $httpUtils;

    /** @var RegisterRequestedScope */
    private $registerRequestedScopeService;

    /**
     * LoginEntryPoint constructor.
     * @param HttpUtils $httpUtils
     * @param RegisterRequestedScope $registerRequestedScopeService
     */
    public function __construct(
        HttpUtils $httpUtils,
        RegisterRequestedScope $registerRequestedScopeService
    ) {
        $this->httpUtils = $httpUtils;
        $this->registerRequestedScopeService = $registerRequestedScopeService;
    }

    public function start(Request $request, AuthenticationException $authenticationException = null)
    {
        $this->registerRequestedScopeService->registerRequestedScope($request);

        return $this->httpUtils->createRedirectResponse($request, 'fos_user_security_login');
    }
}
