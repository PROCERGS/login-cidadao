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

use LoginCidadao\PhoneVerificationBundle\Event\UpdateStatusEvent;
use PROCERGS\Sms\Protocols\SmsStatusInterface;
use PROCERGS\Sms\Protocols\V2\SmsStatus;

class UpdateStatusEventTest extends \PHPUnit_Framework_TestCase
{
    public function testEvent()
    {
        $transactionId = '0123456';
        $details = 'something';
        $date = new \DateTime();
        $status = new SmsStatus($date, $date, SmsStatusInterface::DELIVERED, $details);

        $event = new UpdateStatusEvent($transactionId);
        $this->assertFalse($event->isUpdated());
        $event->setDeliveryStatus($status);

        $this->assertEquals($transactionId, $event->getTransactionId());
        $this->assertEquals($date, $event->getSentAt());
        $this->assertEquals($date, $event->getDeliveredAt());
        $this->assertSame($status, $event->getDeliveryStatus());
        $this->assertTrue($event->isUpdated());
    }

    public function testEventNotChanged()
    {
        $transactionId = '0123456';

        $event = new UpdateStatusEvent($transactionId);

        $this->assertFalse($event->isUpdated());
    }
}
