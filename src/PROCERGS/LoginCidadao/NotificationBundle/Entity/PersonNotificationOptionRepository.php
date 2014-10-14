<?php

namespace PROCERGS\LoginCidadao\NotificationBundle\Entity;

use Doctrine\ORM\EntityRepository;
use PROCERGS\LoginCidadao\CoreBundle\Model\PersonInterface;
use PROCERGS\OAuthBundle\Model\ClientInterface;

class PersonNotificationOptionRepository extends EntityRepository
{

    public function findByClient(PersonInterface $person, ClientInterface $client)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('s')
            ->from('PROCERGSLoginCidadaoNotificationBundle:PersonNotificationOption',
                   's')
            ->join('PROCERGSLoginCidadaoNotificationBundle:Category',
                   'c')
            ->where('s.person = :person')
            ->andWhere('c.client = :client')
            ->setParameters(compact('person', 'client'));

        return $qb->getQuery()->getResult();
    }

}
