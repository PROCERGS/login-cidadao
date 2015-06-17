<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Model\Manager;

use Doctrine\ORM\EntityManager;
use PROCERGS\LoginCidadao\CoreBundle\Entity\CityRepository;

class CityManager implements LocationManagerInterface
{
    /** @var string */
    private $class;

    /** @var CityRepository */
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

    public function findByStateId($id)
    {
        return $this->repository->createQueryBuilder('c')
                ->join('c.state', 's')
                ->where('s.id = :id')
                ->setParameters(compact('id'))
                ->getQuery()->getResult();
    }
}
