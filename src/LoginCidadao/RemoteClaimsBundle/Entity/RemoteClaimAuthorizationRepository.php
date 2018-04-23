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
use LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimAuthorizationInterface;
use LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimInterface;

/**
 * Class RemoteClaimAuthorizationRepository
 * @package LoginCidadao\RemoteClaimsBundle\Entity
 *
 * @codeCoverageIgnore
 */
class RemoteClaimAuthorizationRepository extends EntityRepository
{
    /**
     * @param RemoteClaimAuthorizationInterface $authorization
     * @return RemoteClaimAuthorizationInterface|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findAuthorization(RemoteClaimAuthorizationInterface $authorization)
    {
        return $this->createQueryBuilder('a')
            ->where('a.claimName = :name')
            ->andWhere('a.client = :client')
            ->andWhere('a.person = :person')
            ->setParameters([
                'name' => $authorization->getClaimName(),
                'client' => $authorization->getClient(),
                'person' => $authorization->getPerson(),
            ])
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllByClientAndPerson(ClientInterface $client, PersonInterface $person)
    {
        return $this->createQueryBuilder('a')
            ->where('a.client = :client')
            ->andWhere('a.person = :person')
            ->setParameters([
                'client' => $client,
                'person' => $person,
            ])
            ->getQuery()
            ->getResult();
    }
}
