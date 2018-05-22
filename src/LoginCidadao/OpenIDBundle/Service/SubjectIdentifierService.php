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

use Doctrine\ORM\EntityManagerInterface;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use LoginCidadao\OpenIDBundle\Entity\ClientMetadata;
use LoginCidadao\OpenIDBundle\Entity\SubjectIdentifier;
use LoginCidadao\OpenIDBundle\Entity\SubjectIdentifierRepository;

class SubjectIdentifierService
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var string */
    private $pairwiseSubjectIdSalt;

    /**
     * @var SubjectIdentifierRepository
     */
    private $subjectIdentifierRepo;

    /**
     * SubjectIdentifierService constructor.
     * @param EntityManagerInterface $em
     * @param SubjectIdentifierRepository $subjectIdentifierRepo
     * @param $pairwiseSubjectIdSalt
     */
    public function __construct(
        EntityManagerInterface $em,
        SubjectIdentifierRepository $subjectIdentifierRepo,
        $pairwiseSubjectIdSalt
    ) {
        $this->em = $em;
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
     * @param ClientInterface|ClientMetadata $client
     * @return bool
     */
    public function isSubjectIdentifierPersisted(PersonInterface $person, $client)
    {
        /** @var SubjectIdentifier $sub */
        $sub = $this->getSubjectIdentifierEntity($person, $client);

        return $client !== null && $sub instanceof SubjectIdentifier;
    }

    /**
     * @param $subjectIdentifier
     * @param ClientInterface|null $client
     * @return PersonInterface|null|object
     */
    public function getPerson($subjectIdentifier, ClientInterface $client = null)
    {
        $criteria = ['subjectIdentifier' => $subjectIdentifier];
        if ($client instanceof ClientInterface) {
            $criteria['client'] = $client;
        }

        return $this->subjectIdentifierRepo->findOneBy($criteria);
    }

    /**
     * @param PersonInterface $subject
     * @param ClientMetadata|ClientInterface $client
     * @return mixed|null
     * @internal param ClientInterface|ClientMetadata|null $metadata
     */
    private function fetchSubjectIdentifier(PersonInterface $subject, $client = null)
    {
        if (!$client) {
            return null;
        }

        /** @var SubjectIdentifier $sub */
        $sub = $this->getSubjectIdentifierEntity($subject, $client);

        if ($sub instanceof SubjectIdentifier) {
            return $sub->getSubjectIdentifier();
        }

        return null;
    }

    /**
     * @param PersonInterface $subject
     * @param ClientMetadata $metadata
     * @param string $forceSubjectType
     * @return mixed|string
     */
    private function calculateSubjectIdentifier(
        PersonInterface $subject,
        ClientMetadata $metadata = null,
        $forceSubjectType = null
    ) {
        $id = $subject->getId();

        $subjectType = $this->getSubjectType($metadata);
        if ($forceSubjectType !== null) {
            $subjectType = $forceSubjectType;
        }

        if ($subjectType === 'pairwise' && $metadata instanceof ClientMetadata) {
            $sectorIdentifier = $metadata->getSectorIdentifier();
            $salt = $this->pairwiseSubjectIdSalt;
            $pairwise = hash('sha256', $sectorIdentifier.$id.$salt);

            return $pairwise;
        }

        return $id;
    }

    /**
     * @param PersonInterface $person
     * @param ClientMetadata $metadata
     * @param bool $persist should the created SubjectIdentifier be persisted?
     * @return SubjectIdentifier
     */
    public function enforceSubjectIdentifier(PersonInterface $person, ClientMetadata $metadata, $persist = true)
    {
        $sub = $this->getSubjectIdentifierEntity($person, $metadata);
        if ($sub instanceof SubjectIdentifier) {
            return $sub;
        }

        $sub = (new SubjectIdentifier())
            ->setClient($metadata->getClient())
            ->setPerson($person)
            ->setSubjectIdentifier($this->calculateSubjectIdentifier($person, $metadata));

        if ($persist) {
            $this->em->persist($sub);
            $this->em->flush($sub);
        }

        return $sub;
    }

    /**
     * @param PersonInterface $person
     * @param ClientInterface|ClientMetadata $client
     * @return null|SubjectIdentifier
     */
    private function getSubjectIdentifierEntity(PersonInterface $person, $client)
    {
        if ($client instanceof ClientMetadata) {
            $client = $client->getClient();
        }

        /** @var null|SubjectIdentifier $subjectIdentifier */
        $subjectIdentifier = $this->subjectIdentifierRepo->findOneBy([
            'person' => $person,
            'client' => $client,
        ]);

        return $subjectIdentifier;
    }

    /**
     * @param ClientMetadata $metadata
     * @return string
     */
    private function getSubjectType(ClientMetadata $metadata = null)
    {
        return $metadata instanceof ClientMetadata && $metadata->getSubjectType() === 'pairwise' ? 'pairwise' : 'public';
    }

    /**
     * @param PersonInterface $person
     * @param ClientMetadata $metadata
     * @return SubjectIdentifier|null
     */
    public function convertSubjectIdentifier(PersonInterface $person, ClientMetadata $metadata)
    {
        $sub = $this->getSubjectIdentifierEntity($person, $metadata);

        $newSub = $this->calculateSubjectIdentifier($person, $metadata, 'pairwise');

        $sub->setSubjectIdentifier($newSub);

        return $sub;
    }
}
