<?php

namespace PROCERGS\LoginCidadao\NotificationBundle\Entity;

use Doctrine\ORM\EntityRepository;
use PROCERGS\LoginCidadao\CoreBundle\Model\PersonInterface;
use PROCERGS\OAuthBundle\Model\ClientInterface;
use PROCERGS\LoginCidadao\NotificationBundle\Model\CategoryInterface;

class PersonNotificationOptionRepository extends EntityRepository
{

    public function findByClient(PersonInterface $person,
                                 ClientInterface $client)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('s')
            ->from('PROCERGSLoginCidadaoNotificationBundle:PersonNotificationOption',
                   's')
            ->join('PROCERGSLoginCidadaoNotificationBundle:Category', 'c')
            ->where('s.person = :person')
            ->andWhere('c.client = :client')
            ->setParameters(compact('person', 'client'));

        return $qb->getQuery()->getResult();
    }

    public function findByPerson(PersonInterface $person,
                                 CategoryInterface $category = null,
                                 ClientInterface $client = null)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('s')
            ->from('PROCERGSLoginCidadaoNotificationBundle:PersonNotificationOption',
                   's')
            ->join('PROCERGSLoginCidadaoNotificationBundle:Category', 'c', 'WITH', 's.category = c')
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
