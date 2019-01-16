<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\PhoneVerificationBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use libphonenumber\PhoneNumber;
use LoginCidadao\CoreBundle\Entity\Person;
use LoginCidadao\CoreBundle\Entity\PersonRepository;
use LoginCidadao\CoreBundle\Mailer\TwigSwiftMailer;
use LoginCidadao\CoreBundle\Security\User\Manager\UserManager;
use LoginCidadao\PhoneVerificationBundle\Entity\BlockedPhoneNumber;
use LoginCidadao\PhoneVerificationBundle\Entity\BlockedPhoneNumberRepository;
use LoginCidadao\PhoneVerificationBundle\Model\BlockedPhoneNumberInterface;
use LoginCidadao\PhoneVerificationBundle\Service\Blocklist;
use LoginCidadao\PhoneVerificationBundle\Service\BlocklistOptions;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BlocklistTest extends TestCase
{
    public function testManuallyBlocked()
    {
        $phoneNumber = new PhoneNumber();
        $blocked = $this->createMock(BlockedPhoneNumberInterface::class);

        $blockedPhoneRepository = $this->createMock(BlockedPhoneNumberRepository::class);
        $blockedPhoneRepository->expects($this->once())
            ->method('findByPhone')->with($phoneNumber)
            ->willReturn($blocked);

        $em = $this->getEntityManager([
            BlockedPhoneNumber::class => $blockedPhoneRepository,
            Person::class => $this->createMock(PersonRepository::class),
        ]);

        $options = new BlocklistOptions(2);

        $blocklist = new Blocklist($this->getUserManager(), $this->getMailer(), $em, $options);
        $this->assertTrue($blocklist->isBlocked($phoneNumber));
    }

    public function testAutoBlocked()
    {
        $phoneNumber = new PhoneNumber();

        $blockedPhoneRepository = $this->createMock(BlockedPhoneNumberRepository::class);
        $blockedPhoneRepository->expects($this->once())
            ->method('findByPhone')->with($phoneNumber)
            ->willReturn(null);

        $personRepo = $this->createMock(PersonRepository::class);
        $personRepo->expects($this->once())->method('countByPhone')->willReturn(3);

        $em = $this->getEntityManager([
            BlockedPhoneNumber::class => $blockedPhoneRepository,
            Person::class => $personRepo,
        ]);

        $options = new BlocklistOptions(2);

        $blocklist = new Blocklist($this->getUserManager(), $this->getMailer(), $em, $options);
        $this->assertTrue($blocklist->isBlocked($phoneNumber));
    }

    public function testNotBlocked()
    {
        $phoneNumber = new PhoneNumber();

        $blockedPhoneRepository = $this->createMock(BlockedPhoneNumberRepository::class);
        $blockedPhoneRepository->expects($this->once())
            ->method('findByPhone')->with($phoneNumber)
            ->willReturn(null);

        $em = $this->getEntityManager([
            BlockedPhoneNumber::class => $blockedPhoneRepository,
            Person::class => $this->createMock(PersonRepository::class),
        ]);

        $options = new BlocklistOptions(0);

        $blocklist = new Blocklist($this->getUserManager(), $this->getMailer(), $em, $options);
        $this->assertFalse($blocklist->isBlocked($phoneNumber));
    }

    /**
     * @return MockObject|UserManager
     */
    private function getUserManager()
    {
        return $this->createMock(UserManager::class);
    }

    /**
     * @return MockObject|TwigSwiftMailer
     */
    private function getMailer()
    {
        return $this->createMock(TwigSwiftMailer::class);
    }

    /**
     * @param array $repositories
     * @return MockObject|EntityManagerInterface
     */
    private function getEntityManager(array $repositories)
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->atLeast(2))->method('getRepository')
            ->willReturnCallback(function ($entity) use ($repositories) {
                return $repositories[$entity];
            });

        return $em;
    }
}
