<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use LoginCidadao\OpenIDBundle\Entity\ClientMetadata;

class SectorIdentifierValidator extends ConstraintValidator
{

    public function validate($value, Constraint $constraint)
    {
        if (!($value instanceof ClientMetadata)) {
            $this->context->buildViolation('Invalid class')->addViolation();
            return;
        }

        $redirectUris = $this->parseUris($value->getRedirectUris());
        if ($value->getSectorIdentifierUri() !== null) {
            $sectorIdentifier = parse_url($value->getSectorIdentifierUri());
        } else {
            $sectorIdentifier = null;
        }

        $hosts = array();
        foreach ($redirectUris as $uri) {
            @$hosts[$uri['host']] += 1;
        }

        if (!$sectorIdentifier && count($hosts) > 1) {
            $message = 'sector_identifier_uri is required when multiple hosts are used in redirect_uris. (#rfc.section.8.1)';
            $this->context->buildViolation($message)
                ->atPath('sector_identifier_uri')
                ->setParameter('value', $message)
                ->addViolation();
        }
    }

    /**
     * @param array $uris
     * @return string[][]
     */
    private function parseUris(array $uris)
    {
        return array_map('parse_url', $uris);
    }
}
