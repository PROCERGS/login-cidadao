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
                ->where('c.name LIKE :string')
                ->setParameter('string', "%$string%")
                ->getQuery()->getResult();
    }

}
