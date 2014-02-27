<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

class NotificationRepository extends EntityRepository
{
    public function findAllUnread(Person $person)
    {
        return $this->getEntityManager()
                        ->createQuery('SELECT n FROM PROCERGSLoginCidadaoCoreBundle:Notification n WHERE n.person = :person AND n.isRead = false')
                        ->setParameter('person', $person)
                        ->getResult();
    }
}
