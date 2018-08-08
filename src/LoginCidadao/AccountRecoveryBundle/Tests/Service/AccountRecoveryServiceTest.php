<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\AccountRecoveryBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use libphonenumber\PhoneNumber;
use LoginCidadao\AccountRecoveryBundle\Entity\AccountRecoveryData;
use LoginCidadao\AccountRecoveryBundle\Entity\AccountRecoveryDataRepository;
use LoginCidadao\AccountRecoveryBundle\Service\AccountRecoveryService;
use LoginCidadao\CoreBundle\Entity\Person;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AccountRecoveryServiceTest extends TestCase
{
    public function testGetExistingAccountRecoveryData()
    {
        $person = new Person();
        $data = (new AccountRecoveryData())->setPerson($person);

        $service = new AccountRecoveryService(
            $this->getEntityManager(false),
            $this->getRepository($person, $data)
        );
        $this->assertSame($data, $service->getAccountRecoveryData($person));
    }

    public function testGetNonExistingAccountRecoveryDataAndDontCreate()
    {
        $person = new Person();

        $service = new AccountRecoveryService(
            $this->getEntityManager(false),
            $this->getRepository($person, null)
        );
        $this->assertNull($service->getAccountRecoveryData($person, false));
    }

    public function testGetNonExistingAccountRecoveryDataAndCreate()
    {
        $person = new Person();

        $service = new AccountRecoveryService(
            $this->getEntityManager(true),
            $this->getRepository($person, null)
        );
        $this->assertInstanceOf(AccountRecoveryData::class, $service->getAccountRecoveryData($person));
    }

    public function testSetEmail()
    {
        $person = new Person();
        $service = new AccountRecoveryService(
            $this->getEntityManager(true),
            $this->getRepository($person, null)
        );
        $data = $service->setRecoveryEmail($person, $email = 'email@example.com');
        $this->assertSame($email, $data->getEmail());
    }

    public function testSetPhone()
    {
        $person = new Person();
        $service = new AccountRecoveryService(
            $this->getEntityManager(true),
            $this->getRepository($person, null)
        );
        $data = $service->setRecoveryPhone($person, $phone = new PhoneNumber());
        $this->assertSame($phone, $data->getMobile());
    }

    private function getRepository(PersonInterface $person, AccountRecoveryData $data = null)
    {
        /** @var AccountRecoveryDataRepository|MockObject $repo */
        $repo = $this->createMock(AccountRecoveryDataRepository::class);
        $repo->expects($this->once())->method('findByPerson')
            ->with($person)
            ->willReturn($data);

        return $repo;
    }

    private function getEntityManager(bool $persist)
    {
        /** @var EntityManagerInterface|MockObject $em */
        $em = $this->createMock(EntityManagerInterface::class);
        if ($persist) {
            $em->expects($this->once())->method('persist')->with($this->isInstanceOf(AccountRecoveryData::class));
        } else {
            $em->expects($this->never())->method('persist');
        }

        return $em;
    }
}
