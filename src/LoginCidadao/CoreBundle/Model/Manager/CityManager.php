<?php

namespace LoginCidadao\CoreBundle\Model\Manager;

use Doctrine\ORM\EntityManager;
use LoginCidadao\CoreBundle\Entity\City;
use LoginCidadao\CoreBundle\Entity\CityRepository;

class CityManager implements LocationManagerInterface
{
    /** @var EntityManager */
    private $em;

    /** @var string */
    private $class;

    /** @var CityRepository */
    private $repository;

    public function __construct(EntityManager $em, $class)
    {
        $this->em = $em;
        $this->class = $class;
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
        if (false === is_numeric($id)) {
            return [];
        }

        $reviewed = City::REVIEWED_OK;

        return $this->repository->createQueryBuilder('c')
            ->join('c.state', 's')
            ->where('s.id = :id')
            ->andWhere('c.reviewed = :reviewed')
            ->orderBy('c.name')
            ->setParameters(compact('id', 'reviewed'))
            ->getQuery()->getResult();
    }

    public function createCity(City $city)
    {
        $this->em->persist($city);
        $this->em->flush();
    }
}
