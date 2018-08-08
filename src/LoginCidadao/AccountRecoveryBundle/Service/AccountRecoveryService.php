<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\AccountRecoveryBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use libphonenumber\PhoneNumber;
use LoginCidadao\AccountRecoveryBundle\Entity\AccountRecoveryData;
use LoginCidadao\AccountRecoveryBundle\Entity\AccountRecoveryDataRepository;
use LoginCidadao\AccountRecoveryBundle\Mailer\AccountRecoveryMailer;
use LoginCidadao\CoreBundle\Model\PersonInterface;

class AccountRecoveryService
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var AccountRecoveryDataRepository */
    private $repository;

    /** @var AccountRecoveryMailer */
    private $mailer;

    /**
     * AccountRecoveryService constructor.
     * @param EntityManagerInterface $em
     * @param AccountRecoveryDataRepository $repository
     * @param AccountRecoveryMailer $mailer
     */
    public function __construct(
        EntityManagerInterface $em,
        AccountRecoveryDataRepository $repository,
        AccountRecoveryMailer $mailer
    ) {
        $this->em = $em;
        $this->repository = $repository;
        $this->mailer = $mailer;
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

    public function setRecoveryEmail(PersonInterface $person, string $email = null): AccountRecoveryData
    {
        return $this->getAccountRecoveryData($person)
            ->setEmail($email);
    }

    public function setRecoveryPhone(PersonInterface $person, PhoneNumber $phoneNumber = null): AccountRecoveryData
    {
        return $this->getAccountRecoveryData($person)
            ->setMobile($phoneNumber);
    }

    public function sendPasswordResetEmail(PersonInterface $person)
    {
        $data = $this->getAccountRecoveryData($person);
        if (null !== $data->getEmail()) {
            $this->mailer->sendResettingEmailMessageToRecoveryEmail($data);
        }
    }
}
