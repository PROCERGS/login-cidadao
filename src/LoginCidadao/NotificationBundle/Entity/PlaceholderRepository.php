<?php

namespace LoginCidadao\NotificationBundle\Entity;

use Doctrine\ORM\EntityRepository;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use Doctrine\ORM\QueryBuilder;

class PlaceholderRepository extends EntityRepository
{

    public function findOwnedPlaceholdersByCategoryId(PersonInterface $person,
                                                      $categoryId)
    {
        return $this->getEntityManager()
                ->getRepository('LoginCidadaoNotificationBundle:Placeholder')
                ->createQueryBuilder('p')
                ->join('LoginCidadaoNotificationBundle:Category', 'cat',
                       'WITH', 'p.category = cat')
                ->join('LoginCidadaoOAuthBundle:Client', 'c', 'WITH',
                       'cat.client = c')
                ->where(':person MEMBER OF c.owners')
                ->andWhere('cat.id = :categoryId')
                ->setParameter('person', $person)
                ->setParameter('categoryId', $categoryId)
                ->orderBy('p.id', 'DESC')
                ->getQuery()->getResult();
    }

}
