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

use LoginCidadao\PhoneVerificationBundle\Entity\SentVerification;

class SentVerificationTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $sentVerification = new SentVerification();

        $this->assertInstanceOf(
            'LoginCidadao\PhoneVerificationBundle\Entity\SentVerification',
            $sentVerification
        );
    }

    public function testGettersSetters()
    {
        $sentVerification = new SentVerification();

        $phone = $this->getMock('libphonenumber\PhoneNumber');
        $message = 'some message';
        $transactionId = '1234567890';
        $date = new \DateTime();

        $sentVerification
            ->setMessageSent($message)
            ->setTransactionId($transactionId)
            ->setPhone($phone)
            ->setSentAt($date)
            ->setActuallySentAt($date)
            ->setDeliveredAt($date)
            ->setFinished(true);

        $this->assertNull($sentVerification->getId());
        $this->assertEquals($message, $sentVerification->getMessageSent());
        $this->assertEquals($transactionId, $sentVerification->getTransactionId());
        $this->assertEquals($phone, $sentVerification->getPhone());
        $this->assertInstanceOf('\DateTime', $sentVerification->getSentAt());
        $this->assertEquals($date, $sentVerification->getSentAt());
        $this->assertEquals($date, $sentVerification->getActuallySentAt());
        $this->assertEquals($date, $sentVerification->getDeliveredAt());
        $this->assertTrue($sentVerification->isFinished());
    }
}
