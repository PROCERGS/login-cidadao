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
use Eljam\CircuitBreaker\Exception\CircuitOpenException;
use libphonenumber\PhoneNumber;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\PhoneVerificationBundle\Event\SendPhoneVerificationEvent;
use LoginCidadao\PhoneVerificationBundle\Event\UpdateStatusEvent;
use LoginCidadao\PhoneVerificationBundle\Model\DeliveryStatus;
use LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface;
use LoginCidadao\PhoneVerificationBundle\PhoneVerificationEvents;
use PROCERGS\LoginCidadao\PhoneVerificationBundle\Event\PhoneVerificationSubscriber;
use PROCERGS\LoginCidadao\PhoneVerificationBundle\Event\UpdateSentVerificationSubscriber;

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
        $status = $this->getStatusResponse($transId, $sentAt, $deliveredAt, 'Entregue');

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
        $status = $this->getStatusResponse($transId, null, null, null, 'Protocolo nao encontrado');

        $subscriber = $this->getSubscriber($transId, $status);
        $subscriber->onStatusRequested($event);

        $this->assertTrue($event->isUpdated());
        $this->assertNull($event->getSentAt());
        $this->assertNull($event->getDeliveredAt());
        $this->assertEquals(DeliveryStatus::PROTOCOL_NOT_FOUND, $event->getDeliveryStatus());
    }

    public function testInvalidStatusOnStatusRequested()
    {
        $this->setExpectedException(
            'LoginCidadao\PhoneVerificationBundle\Exception\InvalidSentVerificationStatusException'
        );

        $transId = '0123456';
        $sentAt = new \DateTime();
        $deliveredAt = new \DateTime('+5 minutes');

        $event = new UpdateStatusEvent($transId);
        $status = $this->getStatusResponse($transId, $sentAt, $deliveredAt, 'INVALID');

        $subscriber = $this->getSubscriber($transId, $status);

        return $subscriber->onStatusRequested($event);
    }

    private function getSubscriber($transactionId, $status, $breaker = null)
    {
        $breaker = $breaker ?: new Breaker('breaker');
        $smsService = $this->getMockBuilder('PROCERGS\Sms\SmsService')
            ->disableOriginalConstructor()
            ->getMock();

        $smsService->expects($this->once())->method('getStatus')->with($transactionId)->willReturn($status);

        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $logger->expects($this->once())->method('log');

        $subscriber = new UpdateSentVerificationSubscriber($smsService, $breaker);
        $subscriber->setLogger($logger);

        return $subscriber;
    }

    private function getStatusResponse(
        $transId,
        \DateTime $sentAt = null,
        \DateTime $deliveredAt = null,
        $deliveryStatus = null,
        $sendStatus = null
    ) {
        $javaFormat = 'Y-m-d\TH:i:s.uP';

        $json = json_encode(
            [
                [
                    'numero' => $transId,
                    'dthEnvio' => $sentAt instanceof \DateTime ? $sentAt->format($javaFormat) : null,
                    'dthEntrega' => $deliveredAt instanceof \DateTime ? $deliveredAt->format($javaFormat) : null,
                    'resumoEnvio' => $sendStatus,
                    'resumoEntrega' => $deliveryStatus,
                ],
            ]
        );

        return json_decode($json);
    }
}
