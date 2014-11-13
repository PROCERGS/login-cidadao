<?php

namespace PROCERGS\LoginCidadao\NotificationBundle\Entity;

use Doctrine\ORM\EntityRepository;
use PROCERGS\LoginCidadao\CoreBundle\Model\PersonInterface;
use PROCERGS\OAuthBundle\Model\ClientInterface;
use PROCERGS\LoginCidadao\NotificationBundle\Model\CategoryInterface;
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
            ->from('PROCERGSLoginCidadaoNotificationBundle:PersonNotificationOption',
                   's')
            ->join('PROCERGSLoginCidadaoNotificationBundle:Category', 'c',
                   'WITH', 's.category = c')
            ->innerJoin('PROCERGSOAuthBundle:Client', 'cli', 'WITH',
                        'c.client = cli')
            ->innerJoin('PROCERGSLoginCidadaoCoreBundle:Authorization', 'a',
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
