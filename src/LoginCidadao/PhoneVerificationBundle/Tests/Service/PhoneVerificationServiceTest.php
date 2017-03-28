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

use LoginCidadao\PhoneVerificationBundle\Service\PhoneVerificationService;

class PhoneVerificationServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return PhoneVerificationService
     */
    private function getService()
    {
        $options = $this->getMockBuilder('LoginCidadao\PhoneVerificationBundle\Service\PhoneVerificationOptions')
            ->disableOriginalConstructor()
            ->getMock();

        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->expects($this->once())->method('getRepository');

        return new PhoneVerificationService($options, $em);
    }

    public function testGetPhoneVerification()
    {
        $service = $this->getService();

        $service->getPhoneVerification($person, $phone);
    }
}
