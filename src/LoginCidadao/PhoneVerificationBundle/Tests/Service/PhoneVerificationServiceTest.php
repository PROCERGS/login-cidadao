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
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\PhoneVerificationBundle\Entity\PhoneVerification;
use LoginCidadao\PhoneVerificationBundle\Entity\PhoneVerificationRepository;
use LoginCidadao\PhoneVerificationBundle\Entity\SentVerification;
use LoginCidadao\PhoneVerificationBundle\Entity\SentVerificationRepository;
use LoginCidadao\PhoneVerificationBundle\Event\SendPhoneVerificationEvent;
use LoginCidadao\PhoneVerificationBundle\Exception\VerificationNotSentException;
use LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface;
use LoginCidadao\PhoneVerificationBundle\Model\SentVerificationInterface;
use LoginCidadao\PhoneVerificationBundle\PhoneVerificationEvents;
use LoginCidadao\PhoneVerificationBundle\Service\PhoneVerificationOptions;
use LoginCidadao\PhoneVerificationBundle\Service\PhoneVerificationService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class PhoneVerificationServiceTest extends TestCase
{
    /**
     * @return MockObject|PhoneVerificationInterface
     */
    private function getPhoneVerification()
    {
        return $this->createMock(PhoneVerificationInterface::class);
    }

    /**
     * @return MockObject|PhoneVerificationOptions
     */
    private function getServiceOptions()
    {
        $options = $this->getMockBuilder(PhoneVerificationOptions::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $options;
    }

    /**
     * @return MockObject|EntityManagerInterface
     */
    private function getEntityManager()
    {
        return $this->createMock(EntityManagerInterface::class);
    }

    /**
     * @return MockObject|EventDispatcherInterface
     */
    private function getDispatcher()
    {
        return $this->createMock(EventDispatcherInterface::class);
    }

    private function getRepository($class)
    {
        $repository = $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->getMock();

        return $repository;
    }

    /**
     * @return MockObject|PhoneVerificationRepository
     */
    private function getPhoneVerificationRepository()
    {
        return $this->getRepository(PhoneVerificationRepository::class);
    }

    /**
     * @return MockObject|SentVerificationRepository
     */
    private function getSentVerificationRepository()
    {
        return $this->getRepository(SentVerificationRepository::class);
    }

    /**
     * @return MockObject|PersonRepository
     */
    private function getPersonRepository()
    {
        return $this->getRepository(PersonRepository::class);
    }

    /**
     * @param array $arguments
     * @return PhoneVerificationService
     */
    private function getService(array $arguments = [])
    {
        if (array_key_exists('em', $arguments)) {
            $em = $arguments['em'];
        } else {
            $em = $this->getEntityManager();
        }

        if (array_key_exists('dispatcher', $arguments)) {
            $dispatcher = $arguments['dispatcher'];
        } else {
            $dispatcher = $this->getDispatcher();
        }

        if (array_key_exists('options', $arguments)) {
            $options = $arguments['options'];
        } else {
            $options = $this->getServiceOptions();
            $options->expects($this->any())->method('getLength')->willReturn(6);
            $options->expects($this->any())->method('getVerificationTokenLength')->willReturn(10);
            $options->expects($this->any())->method('isUseNumbers')->willReturn(true);
        }

        if (array_key_exists('phone_verification_repository', $arguments)) {
            $phoneVerificationRepository = $arguments['phone_verification_repository'];
        } else {
            $phoneVerificationRepository = $this->getPhoneVerificationRepository();
        }

        if (array_key_exists('sent_verification_repository', $arguments)) {
            $sentVerificationRepository = $arguments['sent_verification_repository'];
        } else {
            $sentVerificationRepository = $this->getSentVerificationRepository();
        }

        if (array_key_exists('person_repository', $arguments)) {
            $personRepository = $arguments['person_repository'];
        } else {
            $personRepository = $this->getPersonRepository();
        }

        $em->expects($this->exactly(3))->method('getRepository')->willReturnCallback(
            function ($class) use ($phoneVerificationRepository, $sentVerificationRepository, $personRepository) {
                switch ($class) {
                    case PhoneVerification::class:
                        return $phoneVerificationRepository;
                    case SentVerification::class:
                        return $sentVerificationRepository;
                    case Person::class:
                        return $personRepository;
                    default:
                        return null;
                }
            }
        );

        return new PhoneVerificationService($options, $em, $dispatcher);
    }

    public function testGetPhoneVerification()
    {
        $phoneVerification = $this->getPhoneVerification();

        $repository = $this->getPhoneVerificationRepository();
        $repository->expects($this->once())->method('findOneBy')->willReturn($phoneVerification);

        $service = $this->getService(['phone_verification_repository' => $repository]);

        /** @var PersonInterface|MockObject $person */
        $person = $this->createMock(PersonInterface::class);
        $phone = $this->createMock(PhoneNumber::class);

        $this->assertEquals($phoneVerification, $service->getPhoneVerification($person, $phone));
    }

    public function testCreatePhoneVerification()
    {
        $existingPhoneVerification = $this->getPhoneVerification();

        $repository = $this->getPhoneVerificationRepository();
        $repository->expects($this->atLeastOnce())->method('findOneBy')->willReturn($existingPhoneVerification);

        $em = $this->getEntityManager();
        $em->expects($this->once())->method('persist');
        $em->expects($this->once())->method('remove')->with($existingPhoneVerification);
        $em->expects($this->exactly(2))->method('flush');

        $service = $this->getService(['em' => $em, 'phone_verification_repository' => $repository]);

        /** @var PersonInterface|MockObject $person */
        $person = $this->createMock(PersonInterface::class);
        $phone = $this->createMock(PhoneNumber::class);

        $this->assertInstanceOf(PhoneVerificationInterface::class, $service->createPhoneVerification($person, $phone));
    }

    public function testGetPendingPhoneVerification()
    {
        $phoneVerification = $this->getPhoneVerification();

        $repository = $this->getMockBuilder(PhoneVerificationRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $repository->expects($this->once())->method('findOneBy')->willReturn($phoneVerification);
        $repository->expects($this->once())->method('findBy')->willReturn($phoneVerification);

        $service = $this->getService(['phone_verification_repository' => $repository]);

        /** @var PersonInterface|MockObject $person */
        $person = $this->createMock(PersonInterface::class);
        $phone = $this->createMock(PhoneNumber::class);

        $this->assertEquals($phoneVerification, $service->getPendingPhoneVerification($person, $phone));
        $this->assertEquals($phoneVerification, $service->getAllPendingPhoneVerification($person));
    }

    public function testRemovePhoneVerification()
    {
        $em = $this->getEntityManager();
        $em->expects($this->once())->method('remove');
        $em->expects($this->once())->method('flush');

        $service = $this->getService(compact('em'));

        $phoneVerification = $this->getPhoneVerification();
        $this->assertTrue($service->removePhoneVerification($phoneVerification));
    }

    public function testEnforcePhoneVerification()
    {
        $em = $this->getEntityManager();
        $em->expects($this->once())->method('persist');
        $em->expects($this->once())->method('flush');

        $repository = $this->getPhoneVerificationRepository();
        $repository->expects($this->atLeastOnce())->method('findOneBy')->willReturn(null);

        $service = $this->getService(['em' => $em, 'phone_verification_repository' => $repository]);

        /** @var PersonInterface|MockObject $person */
        $person = $this->createMock(PersonInterface::class);
        $phone = $this->createMock(PhoneNumber::class);

        $service->enforcePhoneVerification($person, $phone);
    }

    public function testCheckCaseSensitiveVerificationCode()
    {
        $options = $this->getServiceOptions();
        $options->expects($this->any())->method('isCaseSensitive')->willReturn(true);

        $service = $this->getService(compact('em', 'options'));

        $this->assertTrue($service->checkVerificationCode('abc', 'abc'));
        $this->assertFalse($service->checkVerificationCode('ABC', 'abc'));
    }

    public function testCheckNotCaseSensitiveVerificationCode()
    {
        $options = $this->getServiceOptions();
        $options->expects($this->any())->method('isCaseSensitive')->willReturn(false);

        $service = $this->getService(compact('em', 'options'));

        $this->assertTrue($service->checkVerificationCode('abc', 'abc'));
        $this->assertTrue($service->checkVerificationCode('ABC', 'abc'));
    }

    public function testSuccessfulVerify()
    {
        $phoneVerification = $this->getPhoneVerification();
        $phoneVerification->expects($this->once())->method('setVerifiedAt');
        $phoneVerification->expects($this->atLeastOnce())->method('getVerificationCode')->willReturn('123');

        $em = $this->getEntityManager();
        $em->expects($this->once())->method('persist');
        $em->expects($this->once())->method('flush');

        $dispatcher = $this->getDispatcher();
        $dispatcher->expects($this->once())->method('dispatch');

        $service = $this->getService(compact('em', 'dispatcher'));
        $result = $service->verify($phoneVerification, '123');

        $this->assertTrue($result);
    }

    public function testUnsuccessfulVerify()
    {
        $phoneVerification = $this->getPhoneVerification();
        $phoneVerification->expects($this->never())->method('setVerifiedAt');
        $phoneVerification->expects($this->atLeastOnce())->method('getVerificationCode')->willReturn('321');

        $em = $this->getEntityManager();
        $em->expects($this->never())->method('persist');
        $em->expects($this->never())->method('flush');

        $dispatcher = $this->getDispatcher();
        $dispatcher->expects($this->never())->method('dispatch');

        $service = $this->getService(compact('em', 'dispatcher'));
        $result = $service->verify($phoneVerification, '123');

        $this->assertFalse($result);
    }

    public function testGetPhoneVerificationById()
    {
        $id = random_int(1, 9999);
        $phoneVerification = $this->createMock(PhoneVerificationInterface::class);
        $person = $this->createMock(PersonInterface::class);

        $repository = $this->getPhoneVerificationRepository();
        $repository->expects($this->once())->method('findOneBy')
            ->with(['person' => $person, 'id' => $id])
            ->willReturn($phoneVerification);

        $service = $this->getService(['phone_verification_repository' => $repository]);

        $this->assertEquals($phoneVerification, $service->getPhoneVerificationById($id, $person));
    }

    public function testGetPendingPhoneVerificationById()
    {
        $id = random_int(1, 9999);
        $phoneVerification = $this->createMock(PhoneVerificationInterface::class);
        $person = $this->createMock(PersonInterface::class);

        $repository = $this->getPhoneVerificationRepository();
        $repository->expects($this->once())->method('findOneBy')
            ->with(['person' => $person, 'id' => $id, 'verifiedAt' => null])
            ->willReturn($phoneVerification);

        $service = $this->getService(['phone_verification_repository' => $repository]);

        $this->assertEquals($phoneVerification, $service->getPendingPhoneVerificationById($id, $person));
    }

    public function testSendVerificationCodeSuccess()
    {
        $phoneVerification = $this->getPhoneVerification();

        /** @var SentVerificationInterface|MockObject $sentVerification */
        $sentVerification = $this->createMock(SentVerificationInterface::class);

        $dispatcher = $this->getDispatcher();
        $dispatcher->expects($this->once())->method('dispatch')
            ->with(
                PhoneVerificationEvents::PHONE_VERIFICATION_REQUESTED,
                $this->isInstanceOf(SendPhoneVerificationEvent::class)
            )->willReturnCallback(
                function ($eventName, SendPhoneVerificationEvent $event) use ($sentVerification) {
                    $event->setSentVerification($sentVerification);
                }
            );

        $service = $this->getService(compact('dispatcher'));
        $result = $service->sendVerificationCode($phoneVerification);

        $this->assertEquals($sentVerification, $result);
    }

    public function testSendVerificationCodeFailure()
    {
        $this->expectException(VerificationNotSentException::class);

        $phoneVerification = $this->getPhoneVerification();

        $dispatcher = $this->getDispatcher();
        $dispatcher->expects($this->once())->method('dispatch')
            ->with(
                PhoneVerificationEvents::PHONE_VERIFICATION_REQUESTED,
                $this->isInstanceOf(SendPhoneVerificationEvent::class)
            );

        $service = $this->getService(compact('dispatcher'));
        $service->sendVerificationCode($phoneVerification);
    }

    public function testResendVerificationCodeSuccess()
    {
        $phoneVerification = $this->getPhoneVerification();

        $dispatcher = $this->getDispatcher();
        $dispatcher->expects($this->once())->method('dispatch')
            ->with(
                PhoneVerificationEvents::PHONE_VERIFICATION_REQUESTED,
                $this->isInstanceOf(SendPhoneVerificationEvent::class)
            )->willReturnCallback(
                function ($eventName, SendPhoneVerificationEvent $event) {
                    /** @var SentVerificationInterface|MockObject $sentVerification */
                    $sentVerification = $this->createMock(SentVerificationInterface::class);
                    $event->setSentVerification($sentVerification);
                }
            );

        $service = $this->getService(compact('dispatcher'));
        $service->sendVerificationCode($phoneVerification);
    }

    public function testResendVerificationCodeFailure()
    {
        $this->expectException(TooManyRequestsHttpException::class);
        $sentAt = new \DateTime();
        $sentVerification = $this->createMock(SentVerificationInterface::class);
        $sentVerification->expects($this->once())->method('getSentAt')->willReturn($sentAt);

        $phoneVerification = $this->getPhoneVerification();

        $repository = $this->getSentVerificationRepository();
        $repository->expects($this->once())->method('getLastVerificationSent')->willReturn($sentVerification);

        $options = $this->getServiceOptions();
        $options->expects($this->once())->method('getSmsResendTimeout')->willReturn('+5 minutes');

        $service = $this->getService(['sent_verification_repository' => $repository, 'options' => $options]);
        $service->sendVerificationCode($phoneVerification);
    }

    public function testRegisterVerificationSent()
    {
        /** @var SentVerificationInterface|MockObject $sentVerification */
        $sentVerification = $this->createMock(SentVerificationInterface::class);

        $em = $this->getEntityManager();
        $em->expects($this->once())->method('persist')->with($sentVerification);
        $em->expects($this->once())->method('flush')->with($sentVerification);

        $service = $this->getService(['em' => $em]);
        $service->registerVerificationSent($sentVerification);
    }

    public function testGetNextResendDate()
    {
        $timeout = \DateInterval::createFromDateString('+ 5 minutes');
        $sentAt = new \DateTime("-5 minutes");
        $expected = $sentAt->add($timeout);
        $sentVerification = $this->createMock(SentVerificationInterface::class);
        $sentVerification->expects($this->once())->method('getSentAt')->willReturn($sentAt);

        $repository = $this->getSentVerificationRepository();
        $repository->expects($this->once())->method('getLastVerificationSent')->willReturn($sentVerification);

        $options = $this->getServiceOptions();
        $options->expects($this->once())->method('getSmsResendTimeout')->willReturn('+ 5 minutes');

        $phoneVerification = $this->getPhoneVerification();

        $service = $this->getService(['sent_verification_repository' => $repository, 'options' => $options]);
        $nextResend = $service->getNextResendDate($phoneVerification);

        $this->assertEquals($expected, $nextResend);
    }

    public function testVerifyTokenSuccess()
    {
        $token = 'abc123';

        $phoneVerification = $this->getPhoneVerification();
        $phoneVerification->expects($this->once())->method('isVerified')->willReturn(false);
        $phoneVerification->expects($this->once())->method('getVerificationToken')->willReturn($token);

        $service = $this->getService();
        $this->assertTrue($service->verifyToken($phoneVerification, $token));
    }

    public function testVerifyTokenAlreadyVerified()
    {
        $token = 'abc123';

        $phoneVerification = $this->getPhoneVerification();
        $phoneVerification->expects($this->once())->method('isVerified')->willReturn(true);

        $service = $this->getService();
        $this->assertTrue($service->verifyToken($phoneVerification, $token));
    }

    public function testVerifyTokenInvalidToken()
    {
        $token = 'abc123';

        $phoneVerification = $this->getPhoneVerification();
        $phoneVerification->expects($this->once())->method('isVerified')->willReturn(false);
        $phoneVerification->expects($this->once())->method('getVerificationToken')->willReturn($token);

        $service = $this->getService();
        $this->assertFalse($service->verifyToken($phoneVerification, 'wrong'));
    }

    public function testMandatoryVerification()
    {
        $this->assertTrue($this->isMandatoryTest(3, 3));
        $this->assertTrue($this->isMandatoryTest(4, 3));
    }

    public function testOptionalVerification()
    {
        $this->assertFalse($this->isMandatoryTest(2, 3));
        $this->assertFalse($this->isMandatoryTest(0, 3));
    }

    public function testCountVerified()
    {
        $count = 5;

        /** @var PhoneNumber|MockObject $phoneNumber */
        $phoneNumber = $this->createMock(PhoneNumber::class);

        $repo = $this->getPhoneVerificationRepository();
        $repo->expects($this->once())->method('countVerified')->with($phoneNumber)->willReturn($count);

        $service = $this->getService(['phone_verification_repository' => $repo]);
        $this->assertEquals($count, $service->countVerified($phoneNumber));
    }

    private function isMandatoryTest(int $phoneCount, int $threshold)
    {
        $phoneNumber = $this->createMock(PhoneNumber::class);

        /** @var PhoneVerificationInterface|MockObject $phoneVerification */
        $phoneVerification = $this->createMock(PhoneVerificationInterface::class);
        $phoneVerification->expects($this->once())->method('getPhone')->willReturn($phoneNumber);

        $personRepo = $this->getPersonRepository();
        $personRepo->expects($this->once())->method('countByPhone')->with($phoneNumber)->willReturn($phoneCount);

        $options = new PhoneVerificationOptions(6, true, true, false, false, 10, 6, $threshold);

        $phoneVerificationService = $this->getService([
            'person_repository' => $personRepo,
            'options' => $options,
        ]);

        return $phoneVerificationService->isVerificationMandatory($phoneVerification);
    }
}
