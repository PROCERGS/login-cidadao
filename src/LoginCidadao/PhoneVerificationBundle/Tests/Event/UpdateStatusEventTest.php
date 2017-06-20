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

class UpdateStatusEventTest extends \PHPUnit_Framework_TestCase
{
    public function testEvent()
    {
        $transactionId = '0123456';
        $status = 'Enviado';
        $date = new \DateTime();

        $event = new UpdateStatusEvent($transactionId);
        $event
            ->setSentAt($date)
            ->setDeliveredAt($date)
            ->setDeliveryStatus($status);

        $this->assertEquals($transactionId, $event->getTransactionId());
        $this->assertEquals($date, $event->getSentAt());
        $this->assertEquals($date, $event->getDeliveredAt());
        $this->assertEquals($status, $event->getDeliveryStatus());
        $this->assertTrue($event->isUpdated());
    }

    public function testEventNotChanged()
    {
        $transactionId = '0123456';

        $event = new UpdateStatusEvent($transactionId);

        $this->assertFalse($event->isUpdated());
    }
}
