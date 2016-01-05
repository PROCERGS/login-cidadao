<?php

namespace LoginCidadao\NotificationBundle\Entity;

use Doctrine\ORM\EntityRepository;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
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
            ->getRepository('LoginCidadaoNotificationBundle:FailedCallback')
            ->createQueryBuilder('f')
            ->join('LoginCidadaoNotificationBundle:Notification', 'n',
                   'WITH', 'f.notification = n')
            ->where('n.sender = :client')
            ->setParameter('client', $client);
        return $qb->getQuery()->getResult();
    }

}
