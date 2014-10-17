<?php

namespace PROCERGS\OAuthBundle\Entity;

use Doctrine\ORM\EntityRepository;
use PROCERGS\LoginCidadao\CoreBundle\Model\PersonInterface;

class ClientRepository extends EntityRepository
{

    public function findOneOwned(PersonInterface $person, $id)
    {
        return $this->getEntityManager()
                ->getRepository('PROCERGSOAuthBundle:Client')
                ->createQueryBuilder('c')
                ->where(':person MEMBER OF c.owners')
                ->andWhere('c.id = :id ')
                ->setParameters(compact('person', 'id'))
                ->getQuery()
                ->getOneOrNullResult();
    }

}
