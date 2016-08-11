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
use LoginCidadao\OpenIDBundle\Entity\ClientMetadata;

class SubjectIdentifierService
{
    /** @var string */
    protected $pairwiseSubjectIdSalt;

    public function __construct($pairwiseSubjectIdSalt)
    {
        $this->pairwiseSubjectIdSalt = $pairwiseSubjectIdSalt;
    }

    /**
     * @param PersonInterface $subject
     * @param ClientMetadata|null $metadata
     * @return string
     */
    public function getSubjectIdentifier(PersonInterface $subject, ClientMetadata $metadata = null)
    {
        $id = $subject->getId();
        if ($metadata === null || $metadata->getSubjectType() !== 'pairwise') {
            return $id;
        }

        if ($metadata->getSubjectType() === 'pairwise') {
            $sectorIdentifier = $metadata->getSectorIdentifier();
            $salt = $this->pairwiseSubjectIdSalt;
            $pairwise = hash('sha256', $sectorIdentifier.$id.$salt);

            return $pairwise;
        }
    }
}
