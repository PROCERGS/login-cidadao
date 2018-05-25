<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\PhoneVerificationBundle\Tests\Event;

use Eljam\CircuitBreaker\Breaker;
use libphonenumber\PhoneNumber;
use LoginCidadao\PhoneVerificationBundle\Event\UpdateStatusEvent;
use LoginCidadao\PhoneVerificationBundle\Exception\InvalidSentVerificationStatusException;
use LoginCidadao\PhoneVerificationBundle\PhoneVerificationEvents;
use PROCERGS\LoginCidadao\PhoneVerificationBundle\Event\UpdateSentVerificationSubscriber;
use PROCERGS\Sms\Exception\TransactionNotFoundException;
use PROCERGS\Sms\Protocols\SmsStatusInterface;
use PROCERGS\Sms\Protocols\V2\SmsBuilder;

class UpdateSentVerificationSubscriberTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSubscribedEvents()
    {
        $this->assertArrayHasKey(
            PhoneVerificationEvents::PHONE_VERIFICATION_GET_SENT_VERIFICATION_STATUS,
            UpdateSentVerificationSubscriber::getSubscribedEvents()
        );
    }

    public function testCompleteOnStatusRequested()
    {
        $transId = '0123456';
        $sentAt = new \DateTime();
        $deliveredAt = new \DateTime('+5 minutes');

        $event = new UpdateStatusEvent($transId);
        $status = $this->getStatusResponse($transId, $sentAt, $deliveredAt, SmsStatusInterface::DELIVERED);

        $subscriber = $this->getSubscriber($transId, $status);
        $subscriber->onStatusRequested($event);

        $this->assertTrue($event->isUpdated());
        $this->assertNotNull($event->getSentAt());
        $this->assertNotNull($event->getDeliveredAt());
        $this->assertNotNull($event->getDeliveryStatus());
    }

    public function testTransactionNotFound()
    {
        $transId = '0123456';

        $event = new UpdateStatusEvent($transId);

        $subscriber = $this->getSubscriber($transId, new TransactionNotFoundException());
        $subscriber->onStatusRequested($event);

        $this->assertFalse($event->isUpdated());
        $this->assertNull($event->getSentAt());
        $this->assertNull($event->getDeliveredAt());
        $this->assertNull($event->getDeliveryStatus());
    }

    public function testInvalidStatusOnStatusRequested()
    {
        $this->setExpectedException(
            'LoginCidadao\PhoneVerificationBundle\Exception\InvalidSentVerificationStatusException'
        );

        $transId = '0123456';

        $event = new UpdateStatusEvent($transId);

        $subscriber = $this->getSubscriber($transId, new InvalidSentVerificationStatusException());

        return $subscriber->onStatusRequested($event);
    }

    private function getSubscriber($transactionId, $status, $breaker = null)
    {
        $breaker = $breaker ?: new Breaker('breaker');
        $smsService = $this->getMockBuilder('PROCERGS\Sms\SmsService')
            ->disableOriginalConstructor()
            ->getMock();

        if ($status instanceof \Exception) {
            $smsService->expects($this->once())->method('getStatus')->with($transactionId)->willThrowException($status);
        } else {
            $smsService->expects($this->once())->method('getStatus')->with($transactionId)->willReturn($status);

            $logger = $this->getMock('Psr\Log\LoggerInterface');
            $logger->expects($this->once())->method('log');
        }

        $subscriber = new UpdateSentVerificationSubscriber($smsService, $breaker);
        if (isset($logger)) {
            $subscriber->setLogger($logger);
        }

        return $subscriber;
    }

    private function getStatusResponse(
        $transId,
        \DateTime $sentAt = null,
        \DateTime $deliveredAt = null,
        $statusCode = null
    ) {
        $to = (new PhoneNumber())
            ->setCountryCode(55)
            ->setNationalNumber('51987654321');
        $text = 'dummy';

        return (new SmsBuilder($to, $text))
            ->setId($transId)
            ->setSendDate($sentAt)
            ->setDeliveryDate($deliveredAt)
            ->setStatus($statusCode);
    }
}
