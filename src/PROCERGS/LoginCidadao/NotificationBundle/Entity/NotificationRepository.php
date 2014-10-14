<?php

namespace PROCERGS\LoginCidadao\NotificationBundle\Entity;

use Doctrine\ORM\EntityRepository;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Person;
use Doctrine\ORM\Query;

class NotificationRepository extends EntityRepository
{

    public function findAllUnread(Person $person, $level = null)
    {
        if (is_null($level)) {
            return $this->getEntityManager()
                    ->createQuery('SELECT n FROM PROCERGSLoginCidadaoNotificationBundle:Notification n WHERE n.person = :person AND n.dateRead is null')
                    ->setParameter('person', $person)
                    ->getResult();
        } else {
            return $this->getEntityManager()
                    ->createQuery('SELECT n FROM PROCERGSLoginCidadaoNotificationBundle:Notification n WHERE n.person = :person AND n.dateRead is null AND n.level = :level')
                    ->setParameter('person', $person)
                    ->setParameter('level', $level)
                    ->getResult();
        }
    }

    public function getTotalUnreadGroupByClient($person)
    {
        return $this->getEntityManager()->createQueryBuilder('n')
                ->select('c.id, c.name, CountIf(n.readDate is null) total')
                ->from('PROCERGSLoginCidadaoNotificationBundle:Notification', 'n')
                ->join('PROCERGSLoginCidadaoNotificationBundle:Category', 'cnc', 'WITH', 'n.category = cnc')
                ->join('PROCERGSOAuthBundle:Client', 'c', 'WITH', 'cnc.client = c')
                ->where('n.person = :person')
                ->setParameter('person', $person)
                ->groupBy('c.id', 'c.name')
                ->orderBy('c.id', 'ASC')
                ->getQuery()
                ->getResult();
    }

    public function getTotalUnread($person)
    {
        return $this->getEntityManager()->createQueryBuilder('n')
                ->select('CountIf(n.readDate is null) total')
                ->from('PROCERGSLoginCidadaoNotificationBundle:Notification', 'n')
                ->where('n.person = :person')
                ->setParameter('person', $person)
                ->getQuery()
                ->getResult(Query::HYDRATE_SINGLE_SCALAR);
    }

    public function findUnreadUpToLevel(Person $person, $maxLevel = null)
    {
        if (is_null($maxLevel)) {
            return $this->findAllUnread($person);
        } else {
            return $this->getEntityManager()
                    ->createQuery('SELECT n FROM PROCERGSLoginCidadaoNotificationBundle:Notification n WHERE n.person = :person AND n.readDate is null AND n.level <= :level')
                    ->setParameter('person', $person)
                    ->setParameter('level', $maxLevel)
                    ->getResult();
        }
    }

}
