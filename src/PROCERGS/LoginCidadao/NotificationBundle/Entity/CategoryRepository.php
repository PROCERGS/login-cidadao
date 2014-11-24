<?php

namespace PROCERGS\LoginCidadao\NotificationBundle\Entity;

use Doctrine\ORM\EntityRepository;
use PROCERGS\LoginCidadao\CoreBundle\Model\PersonInterface;
use PROCERGS\OAuthBundle\Model\ClientInterface;
use Doctrine\ORM\QueryBuilder;

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
                       'o', 'WITH', 'o.category = c AND a.person = o.person')
            ->where('o is null')
            ->setParameter('person', $person);

        if (null !== $client) {
            $qb->andWhere('c.client = :client')
                ->setParameter('client', $client);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param PersonInterface $person
     * @return QueryBuilder
     */
    public function getOwnedCategoriesQuery(PersonInterface $person)
    {
        $qb = $this->getEntityManager()
            ->getRepository('PROCERGSLoginCidadaoNotificationBundle:Category')
            ->createQueryBuilder('cat')
            ->join('PROCERGSOAuthBundle:Client', 'c', 'WITH', 'cat.client = c')
            ->where(':person MEMBER OF c.owners')
            ->setParameter('person', $person)
            ->orderBy('cat.id', 'DESC');
        return $qb;
    }

    public function findOwnedCategories(PersonInterface $person)
    {
        return $this->getOwnedCategoriesQuery($person)->getQuery()->getResult();
    }

}
