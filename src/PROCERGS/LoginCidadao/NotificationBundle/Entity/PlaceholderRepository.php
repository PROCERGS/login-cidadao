<?php

namespace PROCERGS\LoginCidadao\NotificationBundle\Entity;

use Doctrine\ORM\EntityRepository;
use PROCERGS\LoginCidadao\CoreBundle\Model\PersonInterface;
use PROCERGS\OAuthBundle\Model\ClientInterface;
use Doctrine\ORM\QueryBuilder;

class PlaceholderRepository extends EntityRepository
{

    public function findOwnedPlaceholdersByCategoryId(PersonInterface $person,
                                                      $categoryId)
    {
        return $this->getEntityManager()
                ->getRepository('PROCERGSLoginCidadaoNotificationBundle:Placeholder')
                ->createQueryBuilder('p')
                ->join('PROCERGSLoginCidadaoNotificationBundle:Category', 'cat',
                       'WITH', 'p.category = cat')
                ->join('PROCERGSOAuthBundle:Client', 'c', 'WITH',
                       'cat.client = c')
                ->where(':person MEMBER OF c.owners')
                ->andWhere('cat.id = :categoryId')
                ->setParameter('person', $person)
                ->setParameter('categoryId', $categoryId)
                ->orderBy('p.id', 'DESC')
                ->getQuery()->getResult();
    }

}
