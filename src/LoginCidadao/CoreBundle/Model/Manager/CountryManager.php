<?php

namespace LoginCidadao\CoreBundle\Model\Manager;

use Doctrine\ORM\EntityManager;
use LoginCidadao\CoreBundle\Entity\Country;
use LoginCidadao\CoreBundle\Entity\CountryRepository;

class CountryManager implements LocationManagerInterface
{
    /** @var string */
    private $class;

    /** @var CountryRepository */
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

    public function findAll()
    {
        return $this->repository->findBy(array('reviewed' => Country::REVIEWED_OK),
                array('name' => 'ASC'));
    }
}
