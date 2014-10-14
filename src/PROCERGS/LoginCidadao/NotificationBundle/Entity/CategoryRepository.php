<?php

namespace PROCERGS\LoginCidadao\NotificationBundle\Entity;

use Doctrine\ORM\EntityRepository;
use PROCERGS\LoginCidadao\CoreBundle\Model\PersonInterface;
use PROCERGS\OAuthBundle\Model\ClientInterface;

class CategoryRepository extends EntityRepository
{

    public function findUnconfigured(PersonInterface $person,
                                     ClientInterface $client = null)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('c')
            ->from('PROCERGSLoginCidadaoNotificationBundle:Category', 'c')
            ->innerJoin('PROCERGSLoginCidadaoCoreBundle:Authorization', 'a',
                        'WITH', 'a.client = c.client AND a.person = :person')
            ->leftJoin('PROCERGSLoginCidadaoNotificationBundle:PersonNotificationOption',
                       'o', 'WITH', 'o.category = c')
            ->where('o is null')
            ->setParameter('person', $person);

        if (null !== $client) {
            $qb->andWhere('c.client = :client')
                ->setParameter('client', $client);
        }

        return $qb->getQuery()->getResult();
    }

}
