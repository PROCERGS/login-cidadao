<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\PhoneVerificationBundle\Tests\Entity;

use LoginCidadao\PhoneVerificationBundle\Entity\PhoneVerification;

class PhoneVerificationTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $phoneVerification = new PhoneVerification();

        $this->assertInstanceOf(
            'LoginCidadao\PhoneVerificationBundle\Entity\PhoneVerification',
            $phoneVerification
        );
    }

    public function testGettersSetters()
    {
        $phoneVerification = new PhoneVerification();

        $person = $this->getMock('LoginCidadao\CoreBundle\Entity\Person');
        $phone = $this->getMock('libphonenumber\PhoneNumber');
        $date = new \DateTime();
        $code = '123456';
        $token = 'abcdef';

        $phoneVerification->setPerson($person)
            ->setPhone($phone)
            ->setVerifiedAt($date)
            ->setVerificationCode($code)
            ->setVerificationToken($token)
            ->setCreatedAtValue();

        $this->assertEquals($person, $phoneVerification->getPerson());
        $this->assertEquals($phone, $phoneVerification->getPhone());
        $this->assertEquals($date, $phoneVerification->getVerifiedAt());
        $this->assertEquals($code, $phoneVerification->getVerificationCode());
        $this->assertEquals($token, $phoneVerification->getVerificationToken());
        $this->assertTrue($phoneVerification->isVerified());
        $this->assertNull($phoneVerification->getId());
        $this->assertNotNull($phoneVerification->getCreatedAt());
        $this->assertInstanceOf('\DateTime', $phoneVerification->getCreatedAt());
    }
}
