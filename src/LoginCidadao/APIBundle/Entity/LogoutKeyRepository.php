<?php

namespace LoginCidadao\APIBundle\Entity;

use Doctrine\ORM\EntityRepository;

class LogoutKeyRepository extends EntityRepository
{

    public function findActiveByKey($key)
    {
        $date = new \DateTime("-5 minutes");
        $dateLimit = $date->format('Y-m-d H:i:s');
        return $this->getEntityManager()
                ->createQuery('SELECT k FROM LoginCidadaoAPIBundle:LogoutKey k WHERE k.logoutKey = :key AND k.createdAt >= :dateLimit')
                ->setParameters(compact('dateLimit', 'key'))
                ->getOneOrNullResult();
    }

}
