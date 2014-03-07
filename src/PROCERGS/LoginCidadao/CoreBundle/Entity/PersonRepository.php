<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

class PersonRepository extends EntityRepository
{

    public function findAllPendingCPF()
    {
        return $this->getEntityManager()
                        ->createQuery('SELECT p FROM PROCERGSLoginCidadaoCoreBundle:Person p WHERE p.cpf IS NULL')
                        ->getResult();
    }

    public function findAllPendingCPFUntilDate($date)
    {
        return $this->getEntityManager()
                        ->createQuery('SELECT p FROM PROCERGSLoginCidadaoCoreBundle:Person p WHERE p.cpf IS NULL AND p.cpfExpiration >= :date')
                        ->setParameter('date', $date)
                        ->getResult();
    }

    public function findUnconfirmedEmailUntilDate(\DateTime $dateLimit)
    {
        return $this->getEntityManager()
                        ->createQuery('SELECT p FROM PROCERGSLoginCidadaoCoreBundle:Person p WHERE p.confirmationToken IS NOT NULL AND p.emailExpiration >= :date')
                        ->setParameter('date', $dateLimit)
                        ->getResult();
    }

}
