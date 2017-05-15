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

use LoginCidadao\PhoneVerificationBundle\PhoneVerificationEvents;
use LoginCidadao\PhoneVerificationBundle\Service\PhoneVerificationService;

class PhoneVerificationServiceTest extends \PHPUnit_Framework_TestCase
{
    private function getPhoneVerification()
    {
        $phoneVerificationClass = 'LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface';
        $phoneVerification = $this->getMock($phoneVerificationClass);

        return $phoneVerification;
    }

    private function getServiceOptions()
    {
        $options = $this->getMockBuilder('LoginCidadao\PhoneVerificationBundle\Service\PhoneVerificationOptions')
            ->disableOriginalConstructor()
            ->getMock();

        return $options;
    }

    private function getEntityManager()
    {
        return $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getDispatcher()
    {
        return $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getRepository($class)
    {
        $repository = $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->getMock();

        return $repository;
    }

    private function getPhoneVerificationRepository()
    {
        return $this->getRepository('LoginCidadao\PhoneVerificationBundle\Entity\PhoneVerificationRepository');
    }

    private function getSentVerificationRepository()
    {
        return $this->getRepository('LoginCidadao\PhoneVerificationBundle\Entity\SentVerificationRepository');
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

        $em->expects($this->exactly(2))->method('getRepository')->willReturnCallback(
            function ($class) use ($phoneVerificationRepository, $sentVerificationRepository) {
                switch ($class) {
                    case 'LoginCidadaoPhoneVerificationBundle:PhoneVerification':
                        return $phoneVerificationRepository;
                    case 'LoginCidadaoPhoneVerificationBundle:SentVerification':
                        return $sentVerificationRepository;
                    default:
                        return null;
                }
            }
        );

        return new PhoneVerificationService($options, $em, $dispatcher);
    }

    public function testGetPhoneVerification()
    {
        $phoneVerificationClass = 'LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface';
        $phoneVerification = $this->getMock($phoneVerificationClass);

        $repository = $this->getPhoneVerificationRepository();
        $repository->expects($this->once())->method('findOneBy')->willReturn($phoneVerification);

        $service = $this->getService(['phone_verification_repository' => $repository]);

        $person = $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');
        $phone = $this->getMock('libphonenumber\PhoneNumber');

        $this->assertEquals($phoneVerification, $service->getPhoneVerification($person, $phone));
    }

    public function testCreatePhoneVerification()
    {
        $phoneVerificationClass = 'LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface';
        $existingPhoneVerification = $this->getMock($phoneVerificationClass);

        $repository = $this->getPhoneVerificationRepository();
        $repository->expects($this->atLeastOnce())->method('findOneBy')->willReturn($existingPhoneVerification);

        $em = $this->getEntityManager();
        $em->expects($this->once())->method('persist');
        $em->expects($this->once())->method('remove')->with($existingPhoneVerification);
        $em->expects($this->exactly(2))->method('flush');

        $service = $this->getService(['em' => $em, 'phone_verification_repository' => $repository]);

        $person = $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');
        $phone = $this->getMock('libphonenumber\PhoneNumber');

        $this->assertInstanceOf($phoneVerificationClass, $service->createPhoneVerification($person, $phone));
    }

    public function testGetPendingPhoneVerification()
    {
        $phoneVerificationClass = 'LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface';
        $phoneVerification = $this->getMock($phoneVerificationClass);

        $repoClass = 'LoginCidadao\PhoneVerificationBundle\Entity\PhoneVerificationRepository';
        $repository = $this->getMockBuilder($repoClass)
            ->disableOriginalConstructor()
            ->getMock();
        $repository->expects($this->once())->method('findOneBy')->willReturn($phoneVerification);
        $repository->expects($this->once())->method('findBy')->willReturn($phoneVerification);

        $service = $this->getService(['phone_verification_repository' => $repository]);

        $person = $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');
        $phone = $this->getMock('libphonenumber\PhoneNumber');

        $this->assertEquals($phoneVerification, $service->getPendingPhoneVerification($person, $phone));
        $this->assertEquals($phoneVerification, $service->getAllPendingPhoneVerification($person));
    }

    public function testRemovePhoneVerification()
    {
        $em = $this->getEntityManager();
        $em->expects($this->once())->method('remove');
        $em->expects($this->once())->method('flush');

        $service = $this->getService(compact('em'));

        $phoneVerificationClass = 'LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface';
        $phoneVerification = $this->getMock($phoneVerificationClass);
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

        $person = $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');
        $phone = $this->getMock('libphonenumber\PhoneNumber');

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
        $phoneVerificationClass = 'LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface';
        $phoneVerification = $this->getMock($phoneVerificationClass);
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
        $phoneVerificationClass = 'LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface';
        $phoneVerification = $this->getMock($phoneVerificationClass);
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
        $phoneVerificationClass = 'LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface';
        $phoneVerification = $this->getMock($phoneVerificationClass);
        $person = $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');

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
        $phoneVerificationClass = 'LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface';
        $phoneVerification = $this->getMock($phoneVerificationClass);
        $person = $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');

        $repository = $this->getPhoneVerificationRepository();
        $repository->expects($this->once())->method('findOneBy')
            ->with(['person' => $person, 'id' => $id, 'verifiedAt' => null])
            ->willReturn($phoneVerification);

        $service = $this->getService(['phone_verification_repository' => $repository]);

        $this->assertEquals($phoneVerification, $service->getPendingPhoneVerificationById($id, $person));
    }

    public function testSendVerificationCodeSuccess()
    {
        $phoneVerificationClass = 'LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface';
        $phoneVerification = $this->getMock($phoneVerificationClass);

        $sentVerification = $this->getMock(
            'LoginCidadao\PhoneVerificationBundle\Model\SentVerificationInterface'
        );

        $dispatcher = $this->getDispatcher();
        $dispatcher->expects($this->once())->method('dispatch')
            ->with(
                PhoneVerificationEvents::PHONE_VERIFICATION_REQUESTED,
                $this->isInstanceOf('LoginCidadao\PhoneVerificationBundle\Event\SendPhoneVerificationEvent')
            )->willReturnCallback(
                function ($eventName, $event) use ($sentVerification) {
                    $event->setSentVerification($sentVerification);
                }
            );

        $service = $this->getService(compact('dispatcher'));
        $result = $service->sendVerificationCode($phoneVerification);

        $this->assertEquals($sentVerification, $result);
    }

    public function testSendVerificationCodeFailure()
    {
        $this->setExpectedException('LoginCidadao\PhoneVerificationBundle\Exception\VerificationNotSentException');

        $phoneVerification = $this->getPhoneVerification();

        $dispatcher = $this->getDispatcher();
        $dispatcher->expects($this->once())->method('dispatch')
            ->with(
                PhoneVerificationEvents::PHONE_VERIFICATION_REQUESTED,
                $this->isInstanceOf('LoginCidadao\PhoneVerificationBundle\Event\SendPhoneVerificationEvent')
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
                $this->isInstanceOf('LoginCidadao\PhoneVerificationBundle\Event\SendPhoneVerificationEvent')
            )->willReturnCallback(
                function ($eventName, $event) {
                    $sentAt = new \DateTime("-5 minutes");
                    $sentVerification = $this->getMock(
                        'LoginCidadao\PhoneVerificationBundle\Model\SentVerificationInterface'
                    );
                    $event->setSentVerification($sentVerification);
                }
            );

        $service = $this->getService(compact('dispatcher'));
        $service->sendVerificationCode($phoneVerification);
    }

    public function testResendVerificationCodeFailure()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException');
        $sentAt = new \DateTime();
        $sentVerification = $this->getMock('LoginCidadao\PhoneVerificationBundle\Model\SentVerificationInterface');
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
        $sentVerification = $this->getMock('LoginCidadao\PhoneVerificationBundle\Model\SentVerificationInterface');

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
        $sentVerification = $this->getMock('LoginCidadao\PhoneVerificationBundle\Model\SentVerificationInterface');
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
}
