<?php
/*
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\StatsBundle\Entity;

use Doctrine\ORM\EntityRepository;

class StatisticsRepository extends EntityRepository
{

    public function findStatsByIndexKeyDate($index, $key = null,
                                            \DateTime $afterDate = null)
    {
        $query = $this->getFindStatsByIndexKeyDateQuery($index, $key, $afterDate);

        return $query->getQuery()->getResult();
    }

    public function getFindStatsByIndexKeyDateQuery($index, $key = null,
                                                    \DateTime $afterDate = null)
    {
        $query = $this->createQueryBuilder('s')
            ->where('s.index = :index')
            ->setParameter('index', $index)
        ;

        if ($key !== null) {
            $query->andWhere('s.key = :key')
                ->setParameter('key', $key);
        }

        if ($afterDate !== null) {
            $query->andWhere('s.timestamp >= :afterDate')
                ->setParameter('afterDate', $afterDate);
        }

        return $query;
    }
}
