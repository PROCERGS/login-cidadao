<?php

namespace LoginCidadao\NotificationBundle\Entity;

use Doctrine\ORM\EntityRepository;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use LoginCidadao\NotificationBundle\Model\CategoryInterface;
use Doctrine\ORM\QueryBuilder;

class PersonNotificationOptionRepository extends EntityRepository
{

    /**
     * @return QueryBuilder
     */
    private function getBaseQuery()
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('s')
            ->from('LoginCidadaoNotificationBundle:PersonNotificationOption',
                    's')
            ->join('LoginCidadaoNotificationBundle:Category', 'c',
                    'WITH', 's.category = c')
            ->innerJoin('LoginCidadaoOAuthBundle:Client', 'cli', 'WITH',
                        'c.client = cli')
            ->innerJoin('LoginCidadaoCoreBundle:Authorization', 'a',
                        'WITH', 'a.client = cli AND a.person = s.person');

        return $qb;
    }

    public function findByClient(PersonInterface $person,
                                    ClientInterface $client)
    {
        return $this->findByPerson($person, null, $client);
    }

    public function findByPerson(PersonInterface $person,
                                    CategoryInterface $category = null,
                                    ClientInterface $client = null)
    {
        $qb = $this->getBaseQuery()
            ->where('s.person = :person')
            ->setParameter('person', $person)
            ->addOrderBy('c.client')
            ->addOrderBy('c.name');

        if (null !== $category) {
            $qb->andWhere('s.category = :category')->setParameter('category',
                                                                    $category);
        }
        if (null !== $client) {
            $qb->andWhere('c.client = :client')->setParameter('client', $client);
        }

        return $qb->getQuery()->getResult();
    }

}
