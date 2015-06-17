<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Model\Manager;

use Doctrine\ORM\EntityManager;
use PROCERGS\LoginCidadao\CoreBundle\Entity\StateRepository;

class StateManager implements LocationManagerInterface
{
    /** @var string */
    private $class;

    /** @var StateRepository */
    private $repository;

    public function __construct(EntityManager $em, $class)
    {
        $this->class      = $class;
        $this->repository = $em->getRepository($this->class);
    }

    public function getClass()
    {
        return $this->class;
    }

    public function find($id)
    {
        return $this->repository->find($id);
    }

    public function findByCountryId($id)
    {
        return $this->repository->createQueryBuilder('s')
                ->join('s.country', 'c')
                ->where('c.id = :id')
                ->setParameters(compact('id'))
                ->getQuery()->getResult();
    }
}
