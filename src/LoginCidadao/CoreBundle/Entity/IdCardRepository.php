<?php

namespace LoginCidadao\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;
use LoginCidadao\CoreBundle\Model\PersonInterface;

class IdCardRepository extends EntityRepository
{

    public function getGridQuery(PersonInterface $person)
    {
        $qb = $this->createQueryBuilder('u')
            ->select('u.id, u.value, right(s.iso6, 2) iso6')
            ->join('LoginCidadaoCoreBundle:State', 's', 'with',
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

    public function findByPersonOrderByStateAcronym(PersonInterface $person)
    {
        $qb = $this->createQueryBuilder('i')
            ->join('LoginCidadaoCoreBundle:State', 's', 'with',
                    'i.state = s')
            ->where('i.person = :person')
            ->setParameters(array('person' => $person))
            ->orderBy('s.acronym', 'asc');

        return $qb->getQuery()->getResult();
    }

}
