<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Validator\Constraints;

use Doctrine\ORM\EntityManager;
use LoginCidadao\OAuthBundle\Entity\Client;
use LoginCidadao\OAuthBundle\Entity\Organization;
use LoginCidadao\OAuthBundle\Entity\OrganizationRepository;
use LoginCidadao\OpenIDBundle\Entity\ClientMetadata;
use LoginCidadao\OpenIDBundle\Validator\SectorIdentifierUriChecker;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class SectorIdentifierUriValidator extends ConstraintValidator
{

    /** @var OrganizationRepository */
    private $orgRepo;

    /** @var SectorIdentifierUriChecker */
    private $uriChecker;

    /**
     * SectorIdentifierUriValidator constructor.
     * @param OrganizationRepository $orgRepo
     */
    public function __construct(OrganizationRepository $orgRepo, SectorIdentifierUriChecker $uriChecker)
    {
        $this->orgRepo = $orgRepo;
        $this->uriChecker = $uriChecker;
    }

    /**
     * @param ClientMetadata $metadata
     * @param Constraint $constraint
     */
    public function validate($metadata, Constraint $constraint)
    {
        if (!$metadata->getSectorIdentifierUri()) {
            return;
        }

        $sectorIdentifierUri = $metadata->getSectorIdentifierUri();

        /** @var Organization $organization */
        $organization = $this->orgRepo->findOneBy(compact('sectorIdentifierUri'));

        $success = $this->uriChecker->check($metadata, $sectorIdentifierUri);
        if (!$success) {
            $metadata->setOrganization(null);
        }

        if ($success && $organization instanceof Organization) {
            $metadata->setOrganization($organization);
        }
    }

    private function buildUrlViolation($message)
    {
        $this->context->buildViolation($message)
            ->atPath('sector_identifier_uri')
            ->addViolation();
    }
}
