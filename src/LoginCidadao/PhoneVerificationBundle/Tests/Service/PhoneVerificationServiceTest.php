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

use Doctrine\ORM\EntityManager;
use LoginCidadao\PhoneVerificationBundle\Entity\PhoneVerificationRepository;
use LoginCidadao\PhoneVerificationBundle\Service\PhoneVerificationService;

class PhoneVerificationServiceTest extends \PHPUnit_Framework_TestCase
{
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

    private function getPhoneVerificationRepository()
    {
        $repoClass = 'LoginCidadao\PhoneVerificationBundle\Entity\PhoneVerificationRepository';
        $repository = $this->getMockBuilder($repoClass)
            ->disableOriginalConstructor()
            ->getMock();

        return $repository;
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
            $options = $this->getMockBuilder('LoginCidadao\PhoneVerificationBundle\Service\PhoneVerificationOptions')
                ->disableOriginalConstructor()
                ->getMock();
            $options->expects($this->any())->method('getLength')->willReturn(6);
            $options->expects($this->any())->method('isUseNumbers')->willReturn(true);
        }

        if (array_key_exists('repository', $arguments)) {
            $repository = $arguments['repository'];
        } else {
            $repository = null;
        }

        $em->expects($this->once())->method('getRepository')->willReturn($repository);

        return new PhoneVerificationService($options, $em, $dispatcher);
    }

    public function testGetPhoneVerification()
    {
        $phoneVerificationClass = 'LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface';
        $phoneVerification = $this->getMock($phoneVerificationClass);

        $repository = $this->getPhoneVerificationRepository();
        $repository->expects($this->once())->method('findOneBy')->willReturn($phoneVerification);

        $service = $this->getService(compact('repository'));

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

        $service = $this->getService(compact('em', 'repository'));

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

        $service = $this->getService(compact('repository'));

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

        $service = $this->getService(compact('repository', 'em'));

        $person = $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');
        $phone = $this->getMock('libphonenumber\PhoneNumber');

        $service->enforcePhoneVerification($person, $phone);
    }

    public function testCheckCaseSensitiveVerificationCode()
    {
        $options = $this->getMockBuilder('LoginCidadao\PhoneVerificationBundle\Service\PhoneVerificationOptions')
            ->disableOriginalConstructor()
            ->getMock();
        $options->expects($this->any())->method('isCaseSensitive')->willReturn(true);

        $service = $this->getService(compact('repository', 'em', 'options'));

        $this->assertTrue($service->checkVerificationCode('abc', 'abc'));
        $this->assertFalse($service->checkVerificationCode('ABC', 'abc'));
    }

    public function testCheckNotCaseSensitiveVerificationCode()
    {
        $options = $this->getMockBuilder('LoginCidadao\PhoneVerificationBundle\Service\PhoneVerificationOptions')
            ->disableOriginalConstructor()
            ->getMock();
        $options->expects($this->any())->method('isCaseSensitive')->willReturn(false);

        $service = $this->getService(compact('repository', 'em', 'options'));

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
}
