<?php
namespace PROCERGS\LoginCidadao\IgpBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use PROCERGS\OAuthBundle\Model\ClientInterface;

class IgpIdCardRepository extends EntityRepository
{

    public function getCountByPerson($person)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('count(igp)')
            ->from('PROCERGSLoginCidadaoIgpBundle:IgpIdCard', 'igp')
            ->join('PROCERGSLoginCidadaoCoreBundle:IdCard', 'i', 'with', 'igp.idCard = i and i.person = :person')
            ->setParameter('person', $person)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getCount()
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('count(igp)')
            ->from('PROCERGSLoginCidadaoIgpBundle:IgpIdCard', 'igp')
            ->join('PROCERGSLoginCidadaoCoreBundle:IdCard', 'i', 'with', 'igp.idCard = i')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
