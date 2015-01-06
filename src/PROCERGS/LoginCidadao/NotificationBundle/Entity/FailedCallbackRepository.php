<?php

namespace PROCERGS\LoginCidadao\NotificationBundle\Entity;

use Doctrine\ORM\EntityRepository;
use PROCERGS\OAuthBundle\Model\ClientInterface;
use Doctrine\ORM\QueryBuilder;

class FailedCallbackRepository extends EntityRepository
{

    /**
     * @param ClientInterface $client
     * @return array
     */
    public function findByClient(ClientInterface $client)
    {
        $qb = $this->getEntityManager()
            ->getRepository('PROCERGSLoginCidadaoNotificationBundle:FailedCallback')
            ->createQueryBuilder('f')
            ->join('PROCERGSLoginCidadaoNotificationBundle:Notification', 'n',
                   'WITH', 'f.notification = n')
            ->where('n.sender = :client')
            ->setParameter('client', $client);
        return $qb->getQuery()->getResult();
    }

}
