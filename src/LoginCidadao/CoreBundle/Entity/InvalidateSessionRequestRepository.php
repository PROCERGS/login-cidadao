<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;
use LoginCidadao\CoreBundle\Model\PersonInterface;

class InvalidateSessionRequestRepository extends EntityRepository
{

    /**
     * @param PersonInterface $person
     * @return InvalidateSessionRequest
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findMostRecent(PersonInterface $person)
    {
        $query = $this->createQueryBuilder('r')
            ->where('r.person = :person')
            ->orderBy('r.requestedAt', 'DESC')
            ->setParameter('person', $person)
            ->setMaxResults(1);

        return $query->getQuery()->getOneOrNullResult();
    }
}
