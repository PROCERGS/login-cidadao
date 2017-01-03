<?php
/*
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\CoreBundle\Helper;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use LoginCidadao\CoreBundle\Entity\PersonRepository;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use PROCERGS\LoginCidadao\CoreBundle\Entity\PersonMeuRS;
use PROCERGS\LoginCidadao\CoreBundle\Entity\PersonMeuRSRepository;

class MeuRSHelper
{
    /** @var EntityManager */
    protected $em;

    /** @var PersonMeuRSRepository */
    protected $personMeuRSRepository;

    public function __construct(
        EntityManager $em,
        EntityRepository $personMeuRSRepository
    ) {
        $this->em = $em;
        $this->personMeuRSRepository = $personMeuRSRepository;
    }

    /**
     * @param \LoginCidadao\CoreBundle\Model\PersonInterface $person
     * @param boolean $create
     * @return PersonMeuRS
     */
    public function getPersonMeuRS(PersonInterface $person, $create = false)
    {
        $personMeuRS = $this->personMeuRSRepository->findOneBy(
            array(
                'person' => $person,
            )
        );

        if ($create && !($personMeuRS instanceof PersonMeuRS)) {
            $personMeuRS = new PersonMeuRS();
            $personMeuRS->setPerson($person);
            $this->em->persist($personMeuRS);
            $this->em->flush($personMeuRS);
        }

        return $personMeuRS;
    }

    public function getVoterRegistration(PersonInterface $person)
    {
        $personMeuRS = $this->getPersonMeuRS($person);

        return $personMeuRS ? $personMeuRS->getVoterRegistration() : null;
    }

    public function setVoterRegistration(
        EntityManager $em,
        PersonInterface $person,
        $value
    ) {
        $personMeuRS = $this->getPersonMeuRS($person, true);

        $personMeuRS->setVoterRegistration($value);
        $em->persist($personMeuRS);
    }

    public function getNfgAccessToken(PersonInterface $person)
    {
        $personMeuRS = $this->getPersonMeuRS($person);

        return $personMeuRS ? $personMeuRS->getNfgAccessToken() : null;
    }

    public function getNfgProfile(PersonInterface $person)
    {
        $personMeuRS = $this->getPersonMeuRS($person);

        return $personMeuRS ? $personMeuRS->getNfgProfile() : null;
    }

    public function findPersonByVoterRegistration($voterRegistration)
    {
        $result = $this->personMeuRSRepository
            ->findOneBy(compact('voterRegistration'));

        return $result ? $result->getPerson() : null;
    }

    /**
     * @param $voterRegistration
     * @return PersonMeuRS|object|null
     */
    public function findPersonMeuRSByVoterRegistration($voterRegistration)
    {
        return $this->personMeuRSRepository
            ->findOneBy(compact('voterRegistration'));
    }

    /**
     * @param string $cpf
     * @param bool $create
     * @return null|PersonMeuRS
     */
    public function getPersonByCpf($cpf, $create = false)
    {
        /** @var PersonInterface|null $person */
        $person = $this->getPersonRepository()->findOneBy(compact('cpf'));

        if (!$person) {
            return null;
        }

        return $this->getPersonMeuRS($person, $create);
    }

    /**
     * @param string $email
     * @param bool $create
     * @return null|PersonMeuRS
     */
    public function getPersonByEmail($email, $create = false)
    {
        /** @var PersonInterface|null $person */
        $person = $this->getPersonRepository()->findOneByEmail($email);

        if (!$person) {
            return null;
        }

        return $this->getPersonMeuRS($person, $create);
    }

    /**
     * @return PersonRepository|EntityRepository
     */
    private function getPersonRepository()
    {
        return $this->em->getRepository('LoginCidadaoCoreBundle:Person');
    }
}
