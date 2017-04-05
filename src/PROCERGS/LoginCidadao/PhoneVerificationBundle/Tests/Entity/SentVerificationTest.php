<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\PhoneVerificationBundle\Tests\Entity;

use PROCERGS\LoginCidadao\PhoneVerificationBundle\Entity\SentVerification;

class SentVerificationTest extends \PHPUnit_Framework_TestCase
{
    public function testSentVerification()
    {
        $phoneVerification = $this->getMock(
            'LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface'
        );
        $sentAt = new \DateTime();
        $messageSent = 'message';
        $transactionId = 1234;

        $sentVerification = new SentVerification();
        $sentVerification
            ->setPhoneVerification($phoneVerification)
            ->setSentAt($sentAt)
            ->setMessageSent($messageSent)
            ->setTransactionId($transactionId);

        $this->assertEquals($phoneVerification, $sentVerification->getPhoneVerification());
        $this->assertEquals($sentAt, $sentVerification->getSentAt());
        $this->assertEquals($messageSent, $sentVerification->getMessageSent());
        $this->assertEquals($transactionId, $sentVerification->getTransactionId());
    }
}
