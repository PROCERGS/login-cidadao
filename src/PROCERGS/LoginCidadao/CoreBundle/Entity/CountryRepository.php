<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

class CountryRepository extends EntityRepository
{

    public function findByString($string)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb
                ->select('c')
                ->from('PROCERGSLoginCidadaoCoreBundle:Country', 'c')
                ->where('c.name LIKE :string OR LOWER(c.name) LIKE :string')
                ->addOrderBy('c.preference', 'DESC')
                ->addOrderBy('c.name', 'ASC')
                ->setParameter('string', "$string%")
                ->getQuery()->getResult();
    }

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

    public function findPreferred()
    {
        return $this->createQueryBuilder('c')
                ->where('c.preference > 0')
                ->addOrderBy('c.preference', 'DESC')
                ->getQuery()->getResult();
    }

}
