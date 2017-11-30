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

class StateRepository extends EntityRepository
{

    public function findByString($string)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb
                ->select('s')
                ->from('LoginCidadaoCoreBundle:State', 's')
                ->join('LoginCidadaoCoreBundle:Country', 'c', 'WITH',
                        's.country = c')
                ->where('s.name LIKE :string OR LOWER(s.name) LIKE :string')
                ->addOrderBy('c.preference', 'DESC')
                ->addOrderBy('s.name', 'ASC')
                ->setParameter('string', "$string%")
                ->getQuery()->getResult();
    }

    public function findOneByString($string)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select('s')
            ->from('LoginCidadaoCoreBundle:State', 's')
            ->orWhere('s.name = :string')
            ->orWhere('s.acronym = :string')
            ->orWhere('s.iso6 = :string')
            ->setParameter('string', $string);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findPreferred()
    {
        return $this->createQueryBuilder('s')
                ->where('s.preference > 0')
                ->addOrderBy('s.preference', 'DESC')
                ->getQuery()->getResult();
    }
    
    public function findStateByPreferredCountry($countryAcronym)
    {
        return $this->createQueryBuilder('s')->join('LoginCidadaoCoreBundle:Country', 'c', 'WITH', 's.country = c')->where('s.reviewed = '.Country::REVIEWED_OK)
                        ->andWhere('c.iso2 = :country')
                        ->setParameter('country', $countryAcronym)
                        ->orderBy('s.name', 'ASC')->getQuery()->getResult(); ;
    }

}
