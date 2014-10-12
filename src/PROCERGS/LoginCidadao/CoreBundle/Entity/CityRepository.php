<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

class CityRepository extends EntityRepository
{

    public function findByString($string)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb
                ->select('c')
                ->from('PROCERGSLoginCidadaoCoreBundle:City', 'c')
                ->join('PROCERGSLoginCidadaoCoreBundle:State', 's', 'WITH', 'c.state = s')
                ->where('c.name LIKE :string')
                ->addOrderBy('s.preference', 'DESC')
                ->addOrderBy('c.name', 'ASC')
                ->setParameter('string', "$string%")
                ->getQuery()->getResult();
    }

}
