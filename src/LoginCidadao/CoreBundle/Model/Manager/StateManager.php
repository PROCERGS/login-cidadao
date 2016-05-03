<?php

namespace LoginCidadao\CoreBundle\Model\Manager;

use Doctrine\ORM\EntityManager;
use LoginCidadao\CoreBundle\Entity\State;
use LoginCidadao\CoreBundle\Entity\StateRepository;

class StateManager implements LocationManagerInterface
{
    /** @var EntityManager */
    private $em;

    /** @var string */
    private $class;

    /** @var StateRepository */
    private $repository;

    public function __construct(EntityManager $em, $class)
    {
        $this->em         = $em;
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
        $reviewed = State::REVIEWED_OK;
        return $this->repository->createQueryBuilder('s')
                ->join('s.country', 'c')
                ->where('c.id = :id')
                ->andWhere('s.reviewed = :reviewed')
                ->orderBy('s.name')
                ->setParameters(compact('id', 'reviewed'))
                ->getQuery()->getResult();
    }

    public function createState(State $state)
    {
        $this->em->persist($state);
        $this->em->flush($state);
    }
}