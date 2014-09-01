<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Entity\Notification;

use Doctrine\ORM\EntityRepository;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Person;

class NotificationRepository extends EntityRepository
{

    public function findAllUnread(Person $person, $level = null)
    {
        if (is_null($level)) {
            return $this->getEntityManager()
                            ->createQuery('SELECT n FROM PROCERGSLoginCidadaoCoreBundle:Notification\Notification n WHERE n.person = :person AND n.isRead = false')
                            ->setParameter('person', $person)
                            ->getResult();
        } else {
            return $this->getEntityManager()
                            ->createQuery('SELECT n FROM PROCERGSLoginCidadaoCoreBundle:Notification\Notification n WHERE n.person = :person AND n.isRead = false AND n.level = :level')
                            ->setParameter('person', $person)
                            ->setParameter('level', $level)
                            ->getResult();
        }
    }

    public function findUnreadUpToLevel(Person $person, $maxLevel = null)
    {
        if (is_null($maxLevel)) {
            return $this->findAllUnread($person);
        } else {
            return $this->getEntityManager()
                            ->createQuery('SELECT n FROM PROCERGSLoginCidadaoCoreBundle:Notification\Notification n WHERE n.person = :person AND n.isRead = false AND n.level <= :level')
                            ->setParameter('person', $person)
                            ->setParameter('level', $maxLevel)
                            ->getResult();
        }
    }

}
