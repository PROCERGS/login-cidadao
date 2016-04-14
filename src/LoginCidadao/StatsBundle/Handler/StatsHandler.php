<?php
/*
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\StatsBundle\Handler;

use LoginCidadao\StatsBundle\Entity\Statistics;
use LoginCidadao\StatsBundle\Entity\StatisticsRepository;

class StatsHandler
{
    /** @var StatisticsRepository */
    protected $repo;

    public function setStatsRepo(StatisticsRepository $repo)
    {
        $this->repo = $repo;
    }

    public function get($index, $key = null, \DateTime $afterDate = null)
    {
        return $this->repo->findStatsByIndexKeyDate($index, $key, $afterDate);
    }

    public function getIndexed($index, $key = null, $days = null)
    {
        return $this->repo->findIndexedStatsByIndexKeyDays(
            $index,
            $key,
            $days
        );
    }

    public function getIndexedUniqueDate(
        $index,
        $keys = null,
        \DateTime $afterDate = null
    ) {
        return $this->repo->findIndexedUniqueStatsByIndexKeyDate(
            $index,
            $keys,
            $afterDate
        );
    }

    public function getIndexedUniqueLastDays($index, $keys = null, $days = null)
    {
        return $this->repo->findIndexedUniqueStatsByIndexKeyDays(
            $index,
            $keys,
            $days
        );
    }

    /**
     * @param \DateTime $date
     * @param $index
     * @param $key
     * @return null|Statistics
     */
    public function getOneByDate(\DateTime $date, $index, $key)
    {
        return $this->repo->findOneBy(
            array(
                'timestamp' => $date,
                'index' => $index,
                'key' => $key,
            )
        );
    }
}
