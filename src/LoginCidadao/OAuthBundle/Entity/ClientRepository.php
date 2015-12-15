<?php

namespace LoginCidadao\OAuthBundle\Entity;

use Doctrine\ORM\EntityRepository;
use LoginCidadao\CoreBundle\Model\PersonInterface;

class ClientRepository extends EntityRepository
{

    public function findOneOwned(PersonInterface $person, $id)
    {
        return $this->createQueryBuilder('c')
                ->where(':person MEMBER OF c.owners')
                ->andWhere('c.id = :id ')
                ->setParameters(compact('person', 'id'))
                ->getQuery()
                ->getOneOrNullResult();
    }

    public function getCountPerson(PersonInterface $person = null,
                                   $clientId = null)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
              ->select('count(a.id) AS qty, c AS client')
              ->from('LoginCidadaoCoreBundle:Authorization', 'a')
            ->innerJoin('LoginCidadaoOAuthBundle:Client', 'c', 'WITH',
                'a.client = c')
              ->where('c.published = true')
              ->groupBy('a.client, c')
            ->orderBy('qty', 'DESC');

        if ($person !== null) {
            $clients = $this->getEntityManager()->createQueryBuilder()
                    ->select('c.id')
                    ->from($this->getEntityName(), 'c', 'c.id')
                    ->join('c.authorizations', 'a')
                    ->where('a.person = :person')
                    ->setParameters(compact('person'))
                    ->getQuery()->getArrayResult();

            $ids = array_keys($clients);
            $qb->orWhere('c.id IN (:clients)')->setParameter('clients', $ids);
    }

        if ($clientId !== null) {
            $qb->andWhere('c.id = :clientId')
                ->setParameter('clientId', $clientId);
        }

        return $qb->getQuery()->getResult();
    }

    public function statsUsersByServiceByDay($days, $clientId = null,
                                             PersonInterface $person = null)
    {
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
            $query->orWhere($query->expr()
                    ->andX('a.createdAt >= :date', 'c.id IN (:clients)')
            )->setParameter('clients', $ids);
        }

        return $query->getQuery()->getScalarResult();
    }
}
