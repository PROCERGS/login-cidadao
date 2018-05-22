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
use Doctrine\ORM\AbstractQuery;

class CountryRepository extends EntityRepository
{

    public function findByString($string)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb
                ->select('c')
                ->from('LoginCidadaoCoreBundle:Country', 'c')
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
                ->from('LoginCidadaoCoreBundle:Country', 'c')
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
    
    public function isPreferred($var)
    {
        return $this->createQueryBuilder('c')
                ->select('count(c) total')
                ->where('c.preference > 0 and c = :country')->setParameter('country', $var)          
                ->getQuery()->getResult(AbstractQuery::HYDRATE_SINGLE_SCALAR);
        
    }

}
