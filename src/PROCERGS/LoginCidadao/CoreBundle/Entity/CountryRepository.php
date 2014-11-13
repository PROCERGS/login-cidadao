<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

class CountryRepository extends EntityRepository
{

    public function findOneByString($string)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb
                ->select('c')
                ->from('PROCERGSLoginCidadaoCoreBundle:Country', 'c')
                ->orWhere('c.name = :string')
                ->orWhere('c.iso2 = :string')
                ->orWhere('c.iso3 = :string')
                ->setParameter('string', $string)
                ->getQuery()->getOneOrNullResult();
    }

}
