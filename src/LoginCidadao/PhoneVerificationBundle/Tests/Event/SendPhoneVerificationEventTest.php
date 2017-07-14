<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\PhoneVerificationBundle\Tests\Event;

use LoginCidadao\PhoneVerificationBundle\Event\SendPhoneVerificationEvent;

class SendPhoneVerificationEventTest extends \PHPUnit_Framework_TestCase
{
    public function testSendPhoneVerificationEvent()
    {
        $phoneVerificationClass = 'LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface';
        $phoneVerification = $this->getMock($phoneVerificationClass);

        $sentVerification = $this->getMock('LoginCidadao\PhoneVerificationBundle\Model\SentVerificationInterface');

        $event = new SendPhoneVerificationEvent($phoneVerification);
        $event->setSentVerification($sentVerification);

        $this->assertEquals($phoneVerification, $event->getPhoneVerification());
        $this->assertEquals($sentVerification, $event->getSentVerification());
    }
}
