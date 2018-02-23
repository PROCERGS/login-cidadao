<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\RemoteClaimsBundle;

class RemoteClaimEvents
{
    /**
     * Event triggered whenever we perform a successful Discovery operation.
     */
    const REMOTE_CLAIM_UPDATE_URI = 'lc.remote_claim.update_uri';
}
