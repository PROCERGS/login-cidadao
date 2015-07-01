<?php
/*
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\TOSBundle\Entity;

use Doctrine\ORM\EntityRepository;
use LoginCidadao\TOSBundle\Model\TOSInterface;

class TermsOfServiceRepository extends EntityRepository
{

    /**
     * @return TOSInterface
     */
    public function findLatestTerms()
    {
        return $this->createQueryBuilder('t')
                ->where('t.final = :final')
                ->orderBy('t.createdAt', 'DESC')
                ->setParameter('final', true)
                ->setMaxResults(1)
                ->getQuery()->getOneOrNullResult();
    }
}
