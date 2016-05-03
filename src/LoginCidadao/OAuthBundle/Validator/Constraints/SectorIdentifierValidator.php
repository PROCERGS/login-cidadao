<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OAuthBundle\Validator\Constraints;

use LoginCidadao\OAuthBundle\Entity\Organization;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class SectorIdentifierValidator extends ConstraintValidator
{

    /**
     * @param Organization $organization
     * @param Constraint $constraint
     */
    public function validate($organization, Constraint $constraint)
    {
        if (!$organization->getSectorIdentifierUri()) {
            return;
        }

        $sectorIdentifierUri = $organization->getSectorIdentifierUri();
        $domain = $organization->getDomain();

        $uri = parse_url($sectorIdentifierUri);

        if ($uri['host'] !== $domain) {
            $this->buildUrlViolation('organizations.validation.error.invalid_domain');
        }
    }

    private function buildUrlViolation($message)
    {
        $this->context->buildViolation($message)
            ->atPath('sectorIdentifierUri')
            ->addViolation();
    }
}
