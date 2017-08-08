<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OAuthBundle\Service;

use LoginCidadao\OAuthBundle\Entity\Organization;
use LoginCidadao\OpenIDBundle\Entity\ClientMetadata;
use LoginCidadao\OpenIDBundle\Validator\SectorIdentifierUriChecker;
use Symfony\Component\HttpKernel\Exception\HttpException;

class OrganizationService
{
    /** @var SectorIdentifierUriChecker */
    private $sectorIdentifierChecker;

    /**
     * OrganizationService constructor.
     * @param SectorIdentifierUriChecker $sectorIdentifierChecker
     */
    public function __construct(SectorIdentifierUriChecker $sectorIdentifierChecker)
    {
        $this->sectorIdentifierChecker = $sectorIdentifierChecker;
    }

    public function getOrganization(ClientMetadata $metadata = null)
    {
        if ($metadata === null) {
            return null;
        }

        if ($metadata->getOrganization() === null && $metadata->getSectorIdentifierUri()) {
            $sectorIdentifierUri = $metadata->getSectorIdentifierUri();
            try {
                $verified = $this->sectorIdentifierChecker->check($metadata, $sectorIdentifierUri);
            } catch (HttpException $e) {
                $verified = false;
            }
            $uri = parse_url($sectorIdentifierUri);
            $domain = $uri['host'];

            $organization = new Organization();
            $organization->setDomain($domain)
                ->setName($domain)
                ->setTrusted(false)
                ->setVerifiedAt($verified ? new \DateTime() : null);

            return $organization;
        }

        return $metadata->getOrganization();
    }
}
