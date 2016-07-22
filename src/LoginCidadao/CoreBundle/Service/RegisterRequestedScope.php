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

class RegisterRequestedScope
{
    /**
     * @param Request $request
     */
    public function registerRequestedScope(Request $request)
    {
        $route = $request->get('_route');

        if ($route === '_authorize_validate') {
            $session = $request->getSession();

            $session->set('requested_scope', $request->get('scope'));
        }
    }

    /**
     * @param Request $request
     */
    public function clearRequestedScope(Request $request)
    {
        $request->getSession()->remove('requested_scope');
    }
}
