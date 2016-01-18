<?php
/*
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OAuthBundle\Entity;

use Doctrine\ORM\EntityRepository;
use LoginCidadao\CoreBundle\Model\PersonInterface;

class OrganizationRepository extends EntityRepository
{

    public function findByMember(PersonInterface $person)
    {
        $query = $this->createQueryBuilder('o')
            ->where(':person MEMBER OF o.members')
            ->setParameters(compact('person'));

        return $query->getQuery()->getResult();
    }

    public function findByNotMember(PersonInterface $person)
    {
        $query = $this->createQueryBuilder('o')
            ->where(':person NOT MEMBER OF o.members')
            ->setParameters(compact('person'));

        return $query->getQuery()->getResult();
    }
}
