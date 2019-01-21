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
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\CoreBundle\Security\User\Manager\UserManager;
use LoginCidadao\PhoneVerificationBundle\Entity\BlockedPhoneNumber;
use LoginCidadao\PhoneVerificationBundle\Entity\BlockedPhoneNumberRepository;
use LoginCidadao\PhoneVerificationBundle\Entity\PhoneVerification;
use LoginCidadao\PhoneVerificationBundle\Entity\PhoneVerificationRepository;
use LoginCidadao\PhoneVerificationBundle\Model\BlockedPhoneNumberInterface;
use LoginCidadao\PhoneVerificationBundle\Service\Blocklist;
use LoginCidadao\PhoneVerificationBundle\Service\BlocklistOptions;
use LoginCidadao\PhoneVerificationBundle\Service\PhoneVerificationServiceInterface;
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

        $em = $this->getEntityManager($blockedPhoneRepository);

        $options = new BlocklistOptions(2);

        $blocklist = new Blocklist($this->getUserManager(), $this->getMailer(), $em,
            $this->getPhoneVerificationService(), $options);
        $this->assertTrue($blocklist->isPhoneBlocked($phoneNumber));
    }

    public function testAutoBlocked()
    {
        $phoneNumber = new PhoneNumber();

        $blockedPhoneRepository = $this->createMock(BlockedPhoneNumberRepository::class);
        $blockedPhoneRepository->expects($this->once())
            ->method('findByPhone')->with($phoneNumber)
            ->willReturn(null);

        $phoneVerification = $this->getPhoneVerificationService();
        $phoneVerification->expects($this->once())->method('countVerified')->willReturn(3);

        $em = $this->getEntityManager($blockedPhoneRepository);

        $options = new BlocklistOptions(2);

        $blocklist = new Blocklist($this->getUserManager(), $this->getMailer(), $em, $phoneVerification, $options);
        $this->assertTrue($blocklist->isPhoneBlocked($phoneNumber));
    }

    public function testNotBlocked()
    {
        $phoneNumber = new PhoneNumber();

        $blockedPhoneRepository = $this->createMock(BlockedPhoneNumberRepository::class);
        $blockedPhoneRepository->expects($this->once())
            ->method('findByPhone')->with($phoneNumber)
            ->willReturn(null);

        $em = $this->getEntityManager($blockedPhoneRepository);

        $options = new BlocklistOptions(0);

        $blocklist = new Blocklist($this->getUserManager(), $this->getMailer(), $em,
            $this->getPhoneVerificationService(), $options);
        $this->assertFalse($blocklist->isPhoneBlocked($phoneNumber));
    }

    public function testBlockByPhone()
    {
        $phoneNumber = new PhoneNumber();
        $users = [
            (new Person())->setMobile($phoneNumber),
            (new Person())->setMobile($phoneNumber),
            (new Person())->setMobile($phoneNumber),
        ];

        $userManager = $this->getUserManager();
        $userManager->expects($this->once())->method('blockUsersByPhone')
            ->with($phoneNumber)->willReturn($users);

        $mailer = $this->getMailer();
        $mailer->expects($this->exactly(count($users)))->method('sendAccountAutoBlockedMessage')
            ->with($this->isInstanceOf(PersonInterface::class));

        $blockedPhoneRepository = $this->createMock(BlockedPhoneNumberRepository::class);

        $em = $this->getEntityManager($blockedPhoneRepository);

        $options = new BlocklistOptions(0);

        $blocklist = new Blocklist($userManager, $mailer, $em, $this->getPhoneVerificationService(), $options);
        $this->assertSame($users, $blocklist->blockByPhone($phoneNumber));
    }

    public function testAddBlockedPhoneNumber()
    {

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
     * @param BlockedPhoneNumberRepository|MockObject $repository
     * @return MockObject|EntityManagerInterface
     */
    private function getEntityManager($repository)
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('getRepository')
            ->with(BlockedPhoneNumber::class)
            ->willReturn($repository);

        return $em;
    }

    /**
     * @return MockObject|PhoneVerificationServiceInterface
     */
    private function getPhoneVerificationService()
    {
        return $this->createMock(PhoneVerificationServiceInterface::class);
    }
}
