<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class RegisterRequestedScope
{
    /**
     * @param Request $request
     */
    public function registerRequestedScope(Request $request)
    {
        $route = $request->get('_route');

        if ($route === '_authorize_validate') {
            $this->registerScope($request->get('scope'), $request->getSession());
        }
    }

    /**
     * @param $scope
     * @param SessionInterface $session
     */
    public function registerScope($scope, SessionInterface $session)
    {
        if (is_array($scope)) {
            $scope = implode(' ', $scope);
        }

        $session->set('requested_scope', $scope);
    }

    /**
     * @param Request $request
     */
    public function clearRequestedScope(Request $request)
    {
        $request->getSession()->remove('requested_scope');
    }
}
