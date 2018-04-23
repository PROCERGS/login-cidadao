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
use LoginCidadao\OAuthBundle\Model\ClientInterface;

/**
 * Class ClientRepository
 * @package LoginCidadao\OAuthBundle\Entity
 * @codeCoverageIgnore
 */
class ClientRepository extends EntityRepository
{

    /**
     * @param PersonInterface $person
     * @param $id
     * @return ClientInterface|Client|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneOwned(PersonInterface $person, $id)
    {
        return $this->createQueryBuilder('c')
            ->where(':person MEMBER OF c.owners')
            ->andWhere('c.id = :id ')
            ->setParameters(compact('person', 'id'))
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getCountPerson(
        PersonInterface $person = null,
        $clientId = null
    ) {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('count(a.id) AS qty, c.id AS client')
            ->from('LoginCidadaoCoreBundle:Authorization', 'a')
            ->innerJoin(
                'LoginCidadaoOAuthBundle:Client',
                'c',
                'WITH',
                'a.client = c'
            )
            ->where('c.published = true')
            ->groupBy('a.client, c.id')
            ->orderBy('qty', 'DESC');

        if ($person !== null) {
            $clients = $this->getEntityManager()->createQueryBuilder()
                ->select('IDENTITY(a.client)')
                ->from('LoginCidadaoCoreBundle:Authorization', 'a')
                ->where('a.person = :person')
                ->setParameter('person', $person)
                ->getQuery()->getScalarResult();

            $qb->orWhere('a.id IN (:clients)')
                ->setParameter('clients', $clients);
        }

        if ($clientId !== null) {
            $qb->andWhere('c.id = :clientId')
                ->setParameter('clientId', $clientId);
        }

        $result = $qb->getQuery()->getResult();

        return $this->injectObject($result, 'client');
    }

    public function statsUsersByServiceByDay(
        $days,
        $clientId = null,
        PersonInterface $person = null
    ) {
        $date = new \DateTime("-$days days");

        $query = $this->createQueryBuilder('c')
            ->select('DATE(a.createdAt) AS day, c.id AS client, COUNT(a.id) AS users')
            ->join('c.authorizations', 'a')
            ->where('a.createdAt >= :date')
            ->andWhere('c.published = true')
            ->groupBy('day, client')
            ->orderBy('day')
            ->setParameter('date', $date);

        if ($clientId !== null) {
            $query
                ->andWhere('a.client = :clientId')
                ->setParameter('clientId', $clientId);
        }

        if ($person !== null) {
            $clients = $this->getEntityManager()->createQueryBuilder()
                ->select('c.id')
                ->from($this->getEntityName(), 'c', 'c.id')
                ->join('c.authorizations', 'a')
                ->where('a.person = :person')
                ->setParameters(compact('person'))
                ->getQuery()->getArrayResult();

            $ids = array_keys($clients);
            $query->orWhere(
                $query->expr()
                    ->andX('a.createdAt >= :date', 'c.id IN (:clients)')
            )->setParameter('clients', $ids);
        }

        return $query->getQuery()->getScalarResult();
    }

    private function injectObject(array $items = [], $idKey)
    {
        $ids = [];
        foreach ($items as $item) {
            $ids[] = $item[$idKey];
        }

        $clients = $this->findBy(['id' => $ids]);
        $indexedClients = [];
        foreach ($clients as $client) {
            $indexedClients[$client->getId()] = $client;
        }

        return array_map(
            function ($item) use ($idKey, $indexedClients) {
                $id = $item[$idKey];
                $item[$idKey] = $indexedClients[$id];

                return $item;
            },
            $items
        );
    }

    /**
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countClients()
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c)')
            ->getQuery()->getSingleScalarResult();
    }

    public function getAccessTokenAccounting(\DateTime $start, \DateTime $end)
    {
        $query = $this->createQueryBuilder('c')
            ->select('c.id, COUNT(a) AS access_tokens')
            ->leftJoin('LoginCidadaoOAuthBundle:AccessToken', 'a', 'WITH', 'a.client = c')
            ->where('a.createdAt BETWEEN :start AND :end')
            ->orWhere('a.id IS NULL')
            ->groupBy('c.id')
            ->setParameters(compact('start', 'end'));

        return $query->getQuery()->getScalarResult();
    }

    public function getActionLogAccounting(\DateTime $start, \DateTime $end)
    {
        $query = $this->createQueryBuilder('c')
            ->select('c.id, COUNT(a) AS api_usage')
            ->leftJoin('LoginCidadaoAPIBundle:ActionLog', 'a', 'WITH', 'a.clientId = c.id')
            ->where('a.createdAt BETWEEN :start AND :end')
            ->orWhere('a.id IS NULL')
            ->groupBy('c.id')
            ->setParameters(compact('start', 'end'));

        return $query->getQuery()->getScalarResult();
    }

    /**
     * @param string[] $redirectUris
     * @return ClientInterface[]
     */
    public function findByRedirectUris(array $redirectUris)
    {
        if (count($redirectUris) <= 0) {
            throw new \InvalidArgumentException('At least one Redirect URI must be passed.');
        }
        $uris = [];
        $query = $this->createQueryBuilder('c');
        foreach ($redirectUris as $k => $uri) {
            $quoted = sprintf('%%"%s"%%', $uri);
            $uris["uri{$k}"] = $quoted;
            $query->orWhere("c.redirectUris LIKE :uri{$k}");
        }

        return $query->setParameters($uris)
            ->getQuery()
            ->getResult();
    }

    public function getOwnedByPersonQuery(PersonInterface $person)
    {
        return $this->createQueryBuilder('c')
            ->where(':person MEMBER OF c.owners')
            ->setParameter('person', $person)
            ->addOrderBy('c.id', 'desc');
    }
}
