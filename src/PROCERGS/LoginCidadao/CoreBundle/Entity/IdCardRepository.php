<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;
use PROCERGS\LoginCidadao\CoreBundle\Model\PersonInterface;

class IdCardRepository extends EntityRepository
{

    public function getGridQuery(PersonInterface $person)
    {
        $qb = $this->createQueryBuilder('u')
            ->select('u.id, u.value, right(s.iso6, 2) iso6')
            ->join('PROCERGSLoginCidadaoCoreBundle:State', 's', 'with',
                   'u.state = s')
            ->where('u.person = :person')
            ->setParameters(array('person' => $person))
            ->orderBy('u.id', 'desc');

        return $qb;
    }

    public function findPersonIdCard(PersonInterface $person, $id)
    {
        return $this->findOneBy(array(
                'person' => $person,
                'id' => $id
        ));
    }

}
