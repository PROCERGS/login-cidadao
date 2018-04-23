<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle;

class LoginCidadaoOpenIDEvents
{
    /**
     * Event triggered when a new authorization is granted.
     */
    const NEW_AUTHORIZATION = 'lc.oidc.new_authorization';

    /**
     * Event triggered when an authorization has to be updated.
     */
    const UPDATE_AUTHORIZATION = 'lc.oidc.update_authorization';

    /**
     * Event triggered when an authorization is revoked.
     */
    const REVOKE_AUTHORIZATION = 'lc.oidc.revoke_authorization';

    /**
     * This event is triggered when we receive an Authorization Request that is not previously granted.
     */
    const NEW_AUTHORIZATION_REQUEST = 'lc.oidc.new_authorization_request';
}
