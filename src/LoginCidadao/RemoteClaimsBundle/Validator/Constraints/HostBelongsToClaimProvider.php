<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\RemoteClaimsBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * HostBelongsToClaimProvider
 * @package LoginCidadao\RemoteClaimsBundle\Validator\Constraints
 *
 * @Annotation
 */
class HostBelongsToClaimProvider extends Constraint
{
    public $message = "The host '{{ host }}' present in claim_name is not present in any of the Claim Provider's redirect_uris.";
}
