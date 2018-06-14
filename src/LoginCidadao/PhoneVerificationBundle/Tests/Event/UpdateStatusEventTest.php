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
use LoginCidadao\PhoneVerificationBundle\Model\SmsStatusInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UpdateStatusEventTest extends TestCase
{
    public function testEvent()
    {
        $transactionId = '0123456';
        $date = new \DateTime();

        /** @var SmsStatusInterface|MockObject $status */
        $status = $this->createMock('LoginCidadao\PhoneVerificationBundle\Model\SmsStatusInterface');
        $status->expects($this->once())->method('getDateSent')->willReturn($date);
        $status->expects($this->once())->method('getDateDelivered')->willReturn($date);

        $event = new UpdateStatusEvent($transactionId);
        $this->assertNull($event->getSentAt());
        $this->assertNull($event->getDeliveredAt());
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
