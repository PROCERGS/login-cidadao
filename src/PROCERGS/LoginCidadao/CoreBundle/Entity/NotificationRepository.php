<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

class NotificationRepository extends EntityRepository
{

    public function findAllUnread(Person $person, $level = null)
    {
        if (is_null($level)) {
            return $this->getEntityManager()
                            ->createQuery('SELECT n FROM PROCERGSLoginCidadaoCoreBundle:Notification n WHERE n.person = :person AND n.isRead = false')
                            ->setParameter('person', $person)
                            ->getResult();
        } else {
            return $this->getEntityManager()
                            ->createQuery('SELECT n FROM PROCERGSLoginCidadaoCoreBundle:Notification n WHERE n.person = :person AND n.isRead = false AND n.level = :level')
                            ->setParameter('person', $person)
                            ->setParameter('level', $level)
                            ->getResult();
        }
    }

}
