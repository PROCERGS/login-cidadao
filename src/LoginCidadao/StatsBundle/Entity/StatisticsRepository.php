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
use Doctrine\ORM\QueryBuilder;

class StatisticsRepository extends EntityRepository
{

    public function findIndexedStatsByIndexKeyDays(
        $index,
        $keys = null,
        $days = null
    ) {
        $data = $this->findStatsByIndexKeyDays($index, $keys, $days);

        return $this->indexResults($data);
    }

    public function findStatsByIndexKeyDays(
        $index,
        $keys = null,
        $days = null
    ) {
        $query = $this->getFindStatsByIndexKeyDateQuery(
            $index,
            $keys,
            null,
            $days
        );

        return $query->getQuery()->getResult();
    }
    public function findStatsByIndexKeyDate(
        $index,
        $keys = null,
        \DateTime $afterDate = null
    ) {
        $query = $this->getFindStatsByIndexKeyDateQuery(
            $index,
            $keys,
            $afterDate
        );

        return $query->getQuery()->getResult();
    }

    public function getFindStatsByIndexKeyDateQuery(
        $index,
        $keys = null,
        \DateTime $afterDate = null,
        $days = null
    ) {
        $query = $this->createQueryBuilder('s')
            ->where('s.index = :index')
            ->setParameter('index', $index);

        if ($keys !== null) {
            $query->andWhere('s.key IN (:keys)')
                ->setParameter('keys', $keys);
        }

        if ($afterDate !== null) {
            $query->andWhere('s.timestamp >= :afterDate')
                ->setParameter('afterDate', $afterDate);
        }

        if ($days !== null) {
            $query->setMaxResults($days)
                ->orderBy('s.timestamp', 'DESC');
        }

        return $query;
    }

    /**
     * @param Statistics[] $data
     * @return Statistics[]
     */
    public function indexResults(array $data)
    {
        $result = array();
        foreach ($data as $entry) {
            $result[$entry->getKey()][] = $entry;
        }

        return $result;
    }

    public function findIndexedUniqueStatsByIndexKeyDate(
        $index,
        $keys = null,
        \DateTime $afterDate = null
    ) {
        $query = $this->getFindStatsByIndexKeyDateQuery(
            $index,
            $keys,
            $afterDate
        );
        $this->applyGreatestNPerGroupDate($query);
        $data = $query->getQuery()->getResult();

        return $this->indexResults($data);
    }

    public function applyGreatestNPerGroupDate(QueryBuilder $qb)
    {
        $qb->distinct();
        $qb2 = $this->createQueryBuilder('ss')
            ->select('MAX(ss.timestamp)')
            ->where('DATE(ss.timestamp) = DATE(s.timestamp)');

        $qb->innerJoin(
            $this->getEntityName(),
            'sub',
            'WITH',
            $qb->expr()->eq('s.timestamp', '('.$qb2->getDQL().')')
        );
    }

    public function findIndexedUniqueStatsByIndexKeyDays(
        $index,
        $keys = null,
        $days = null
    ) {
        $query = $this->getFindStatsByIndexKeyDateQuery(
            $index,
            $keys,
            null,
            $days
        );
        $this->applyGreatestNPerGroupDate($query);
        $data = $query->getQuery()->getResult();

        return $this->indexResults($data);
    }
}
