<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\RemoteClaimsBundle\Entity;

use Doctrine\ORM\EntityRepository;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimInterface;

/**
 * Class RemoteClaimRepository
 * @package LoginCidadao\RemoteClaimsBundle\Entity
 *
 * @codeCoverageIgnore
 */
class RemoteClaimRepository extends EntityRepository
{
    /**
     * @param ClientInterface $client
     * @return array|RemoteClaimInterface[]
     */
    public function findByClient(ClientInterface $client)
    {
        return $this->createQueryBuilder('r')
            ->where('r.provider = :client')
            ->setParameter('client', $client)
            ->getQuery()
            ->getResult();
    }
}
