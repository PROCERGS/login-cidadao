<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

class StateRepository extends EntityRepository
{

    public function findOneByString($string)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select('s')
            ->from('PROCERGSLoginCidadaoCoreBundle:State', 's')
            ->orWhere('s.name = :string')
            ->orWhere('s.acronym = :string')
            ->orWhere('s.iso6 = :string')
            ->setParameter('string', $string);

        return $qb->getQuery()->getOneOrNullResult();
    }

}
