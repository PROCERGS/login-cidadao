<?php

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
}
