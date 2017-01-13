<?php
/**
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

class AccessTokenRepository extends EntityRepository
{

    public function getAccounting(\DateTime $start, \DateTime $end)
    {
        $query = $this->createQueryBuilder('a')
            ->select('c.id, COUNT(a) AS access_tokens')
            ->join('a.client','c')
            ->where('a.createdAt BETWEEN :start AND :end')
            ->groupBy('c.id')
            ->setParameters(compact('start', 'end'));

        return $query->getQuery()->getScalarResult();
    }
}
