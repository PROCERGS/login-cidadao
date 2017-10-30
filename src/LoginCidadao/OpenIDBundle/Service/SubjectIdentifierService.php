<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Service;

use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use LoginCidadao\OpenIDBundle\Entity\ClientMetadata;
use LoginCidadao\OpenIDBundle\Entity\SubjectIdentifier;
use LoginCidadao\OpenIDBundle\Entity\SubjectIdentifierRepository;

class SubjectIdentifierService
{
    /** @var string */
    private $pairwiseSubjectIdSalt;

    /**
     * @var SubjectIdentifierRepository
     */
    private $subjectIdentifierRepo;

    /**
     * SubjectIdentifierService constructor.
     * @param SubjectIdentifierRepository $subjectIdentifierRepo
     * @param $pairwiseSubjectIdSalt
     */
    public function __construct(SubjectIdentifierRepository $subjectIdentifierRepo, $pairwiseSubjectIdSalt)
    {
        $this->subjectIdentifierRepo = $subjectIdentifierRepo;
        $this->pairwiseSubjectIdSalt = $pairwiseSubjectIdSalt;
    }

    /**
     * @param PersonInterface $subject
     * @param ClientMetadata|null $metadata
     * @param bool $fetch do not try to fetch from the DB. Used as a performance boost for the subject identifier generator command.
     * @return string
     */
    public function getSubjectIdentifier(PersonInterface $subject, ClientMetadata $metadata = null, $fetch = true)
    {
        $sub = $fetch ? $this->fetchSubjectIdentifier($subject, $metadata) : null;

        if ($sub === null) {
            return $this->calculateSubjectIdentifier($subject, $metadata);
        }

        return $sub;
    }

    /**
     * @param PersonInterface $person
     * @param ClientInterface $client
     * @return bool
     */
    public function isSubjectIdentifierPersisted(PersonInterface $person, ClientInterface $client)
    {
        /** @var SubjectIdentifier $sub */
        $sub = $this->subjectIdentifierRepo->findOneBy(
            [
                'person' => $person,
                'client' => $client,
            ]
        );

        return $sub instanceof SubjectIdentifier;
    }

    /**
     * @param PersonInterface $subject
     * @param ClientMetadata|null $metadata
     * @return mixed|null
     */
    private function fetchSubjectIdentifier(PersonInterface $subject, ClientMetadata $metadata = null)
    {
        if (!$metadata) {
            return null;
        }
        /** @var SubjectIdentifier $sub */
        $sub = $this->subjectIdentifierRepo->findOneBy(
            [
                'person' => $subject,
                'client' => $metadata->getClient(),
            ]
        );

        if ($sub instanceof SubjectIdentifier) {
            return $sub->getSubjectIdentifier();
        }

        return null;
    }

    private function calculateSubjectIdentifier(PersonInterface $subject, ClientMetadata $metadata = null)
    {
        $id = $subject->getId();

        if ($metadata instanceof ClientMetadata && $metadata->getSubjectType() === 'pairwise') {
            $sectorIdentifier = $metadata->getSectorIdentifier();
            $salt = $this->pairwiseSubjectIdSalt;
            $pairwise = hash('sha256', $sectorIdentifier.$id.$salt);

            return $pairwise;
        }

        return $id;
    }
}
