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
use LoginCidadao\CoreBundle\Model\PersonInterface;
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

    public function findByClientAndPerson(ClientInterface $client, PersonInterface $person)
    {
        return $this->createQueryBuilder('r')
            ->select('r')
            ->innerJoin(
                'LoginCidadaoRemoteClaimsBundle:RemoteClaimAuthorization',
                'a', 'WITH', 'a.client = :client AND a.person = :person'
            )
            ->where('a.claimName = r.name')
            ->setParameters([
                'client' => $client,
                'person' => $person,
            ])
            ->getQuery()
            ->getResult();
    }
}
