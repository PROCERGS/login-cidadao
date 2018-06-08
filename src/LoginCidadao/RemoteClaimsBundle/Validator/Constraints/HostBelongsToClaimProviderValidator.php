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

use LoginCidadao\RemoteClaimsBundle\Model\HttpUri;
use LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class HostBelongsToClaimProviderValidator extends ConstraintValidator
{

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed $value The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value instanceof RemoteClaimInterface) {

            $host = $value->getName()->getAuthorityName();
            $provider = $value->getProvider();

            $redirectUris = $provider->getRedirectUris();
            foreach ($redirectUris as $redirectUri) {
                $uri = HttpUri::parseUri($redirectUri);
                if ($uri['host'] === $host) {
                    return;
                }
            }


            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ host }}', $host)
                ->addViolation();
        }
    }
}
