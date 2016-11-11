<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

class PersonMeuRSRepository extends EntityRepository
{
    /**
     * @param string $cpf
     * @return PersonMeuRS|null
     */
    public function getOneByCpf($cpf)
    {
        return $this->createQueryBuilder('rs')
            ->join('rs.person', 'p')
            ->where('p.cpf = :cpf')
            ->setParameter('cpf', $cpf)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param string $email
     * @return PersonMeuRS|null
     */
    public function getOneByEmail($email)
    {
        return $this->createQueryBuilder('rs')
            ->join('rs.person', 'p')
            ->where('p.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
