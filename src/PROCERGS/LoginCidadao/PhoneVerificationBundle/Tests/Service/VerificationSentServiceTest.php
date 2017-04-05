<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\PhoneVerificationBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use PROCERGS\LoginCidadao\PhoneVerificationBundle\Entity\SentVerificationRepository;
use PROCERGS\LoginCidadao\PhoneVerificationBundle\Service\VerificationSentService;

class VerificationSentServiceTest extends \PHPUnit_Framework_TestCase
{
    private function getRepo()
    {
        $class = 'PROCERGS\LoginCidadao\PhoneVerificationBundle\Entity\SentVerificationRepository';

        return $repository = $this
            ->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getEntityManager(SentVerificationRepository $repository = null)
    {
        $repository = $repository ?: $this->getRepo();

        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()->getMock();
        $em->expects($this->once())->method('getRepository')->willReturn($repository);

        return $em;
    }

    private function getVerificationSentService(EntityManagerInterface $em = null)
    {
        $em = $em ?: $this->getEntityManager();

        $service = new VerificationSentService($em);

        return $service;
    }

    public function testGetLastVerificationSent()
    {
        $sentVerification = $this->getMock(
            'PROCERGS\LoginCidadao\PhoneVerificationBundle\Model\SentVerificationInterface'
        );

        $phoneVerification = $this->getMock('LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface');

        $repo = $this->getRepo();
        $repo->expects($this->once())->method('getLastVerificationSent')->with($phoneVerification)
            ->willReturn($sentVerification);

        $em = $this->getEntityManager($repo);

        $service = $this->getVerificationSentService($em);
        $response = $service->getLastVerificationSent($phoneVerification);

        $this->assertEquals($sentVerification, $response);
    }

    public function testRegisterVerificationSent()
    {
        $phoneVerification = $this->getMock('LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface');
        $transactionId = '0123456';
        $message = 'message';

        $em = $this->getEntityManager();
        $em->expects($this->once())->method('persist')
            ->with(
                $this->isInstanceOf('PROCERGS\LoginCidadao\PhoneVerificationBundle\Model\SentVerificationInterface')
            );
        $em->expects($this->once())->method('flush');

        $service = $this->getVerificationSentService($em);
        $service->registerVerificationSent($phoneVerification, $transactionId, $message);
    }
}
