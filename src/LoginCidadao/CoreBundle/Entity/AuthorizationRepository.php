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

class AuthorizationRepository extends EntityRepository
{

    public function statsUsersByServiceByDay($days, $clientId = null)
    {
        $date = new \DateTime("-$days days");

        $query = $this->createQueryBuilder('a')
            ->select('c AS client, DATE(a.createdAt) AS day, COUNT(a.id) AS users')
            ->join('a.client', 'c')
            ->where('a.createdAt >= :date')
            ->groupBy('day, c')
            ->setParameter('date', $date);

        if ($clientId !== null) {
            $query
                ->andWhere('a.client = :clientId')
                ->setParameter('clientId', $clientId);
        }

        return $query->getQuery()->getResult();
    }

    public function statsUsersByServiceByDayOfWeek($clientId = null)
    {
        $query = $this->createQueryBuilder('a')
            ->select('DAYOFWEEK(a.createdAt) AS day_of_week, COUNT(a.id) AS users')
            ->groupBy('day_of_week');

        if ($clientId !== null) {
            $query
                ->where('a.client = :clientId')
                ->setParameter('clientId', $clientId);
        }

        return $query->getQuery()->getScalarResult();
    }

    public function statsUsersByServiceVisibility($visible = true, $uid = null)
    {
        $query = $this->createQueryBuilder('a')
            ->select("CONCAT(c.id, '_', c.randomId) AS id, c.name, c.siteUrl AS uri, m.logo_uri, c.uid, COUNT(a.id) AS users")
            ->join('a.client', 'c')
            ->leftJoin('c.metadata', 'm')
            ->where('c.visible = :visible')
            ->setParameter('visible', $visible)
            ->groupBy('c, m');

        if ($visible && !$uid) {
            $query->orWhere('c.uid IS NOT NULL');
        }
        if ($visible && null !== $uid) {
            $query->orWhere('c.uid = :uid')
                ->setParameter('uid', $uid);
        }

        return $query->getQuery()->getScalarResult();
    }
}
