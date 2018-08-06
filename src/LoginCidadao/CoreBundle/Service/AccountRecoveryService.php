<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use libphonenumber\PhoneNumber;
use LoginCidadao\CoreBundle\Entity\AccountRecoveryData;
use LoginCidadao\CoreBundle\Entity\AccountRecoveryDataRepository;
use LoginCidadao\CoreBundle\Model\PersonInterface;

class AccountRecoveryService
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var AccountRecoveryDataRepository */
    private $repository;

    /**
     * AccountRecoveryService constructor.
     * @param EntityManagerInterface $em
     * @param AccountRecoveryDataRepository $repository
     */
    public function __construct(EntityManagerInterface $em, AccountRecoveryDataRepository $repository)
    {
        $this->em = $em;
        $this->repository = $repository;
    }

    public function getAccountRecoveryData(PersonInterface $person, bool $createIfNotFound = true): ?AccountRecoveryData
    {
        /** @var AccountRecoveryData $data */
        $data = $this->repository->findByPerson($person);
        if (null === $data && $createIfNotFound) {
            $data = (new AccountRecoveryData())
                ->setPerson($person);
            $this->em->persist($data);
        }

        return $data;
    }

    public function setRecoveryEmail(PersonInterface $person, string $email): AccountRecoveryData
    {
        return $this->getAccountRecoveryData($person)
            ->setEmail($email);
    }

    public function setRecoveryPhone(PersonInterface $person, PhoneNumber $phoneNumber): AccountRecoveryData
    {
        return $this->getAccountRecoveryData($person)
            ->setMobile($phoneNumber);
    }
}
