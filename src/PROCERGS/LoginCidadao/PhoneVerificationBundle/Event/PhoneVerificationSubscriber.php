<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\PhoneVerificationBundle\Event;

use Eljam\CircuitBreaker\Breaker;
use Eljam\CircuitBreaker\Exception\CircuitOpenException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\PhoneVerificationBundle\Entity\SentVerification;
use LoginCidadao\PhoneVerificationBundle\Event\SendPhoneVerificationEvent;
use LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface;
use LoginCidadao\PhoneVerificationBundle\PhoneVerificationEvents;
use PROCERGS\Sms\Exception\SmsServiceException;
use PROCERGS\Sms\SmsService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

class PhoneVerificationSubscriber implements EventSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait, LoggerTrait;

    /** @var SmsService */
    private $smsService;

    /** @var TranslatorInterface */
    private $translator;

    /** @var RouterInterface */
    private $router;

    /** @var Breaker */
    private $breaker;

    /**
     * PhoneVerificationSubscriber constructor.
     *
     * @param SmsService $smsService
     * @param TranslatorInterface $translator
     * @param RouterInterface $router
     * @param Breaker $breaker
     */
    public function __construct(
        SmsService $smsService,
        TranslatorInterface $translator,
        RouterInterface $router,
        Breaker $breaker
    ) {
        $this->smsService = $smsService;
        $this->translator = $translator;
        $this->router = $router;
        $this->breaker = $breaker;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    protected function log($level, $message, array $context = array())
    {
        if ($this->logger) {
            $this->logger->log($level, $message, $context);
        }
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            PhoneVerificationEvents::PHONE_VERIFICATION_REQUESTED => 'onVerificationRequest',
        ];
    }

    public function onVerificationRequest(
        SendPhoneVerificationEvent $event,
        $eventName,
        EventDispatcherInterface $dispatcher
    ) {
        $phoneVerification = $event->getPhoneVerification();
        $code = $phoneVerification->getVerificationCode();
        $link = $this->router->generate(
            'lc_phone_verification_verify_link',
            ['id' => $phoneVerification->getId(), 'token' => $phoneVerification->getVerificationToken()],
            RouterInterface::ABSOLUTE_URL
        );

        $message = $this->translator->trans('phone_verification.sms.message', ['%code%' => $code, '%link%' => $link]);

        try {
            $this->sendSmsAndRegister($event, $dispatcher, $message);
        } catch (CircuitOpenException $e) {
            $this->logSmsFailed($phoneVerification->getPerson(), $phoneVerification, 'SMS Service unavailable.');
        } catch (\Exception $e) {
            $this->logSmsFailed($phoneVerification->getPerson(), $phoneVerification, $e->getMessage(), $e->getCode());
        }
    }

    private function sendSmsAndRegister(
        SendPhoneVerificationEvent $event,
        EventDispatcherInterface $dispatcher,
        $message
    ) {
        $phoneVerification = $event->getPhoneVerification();
        $transactionId = $this->protectedSendSms($this->smsService, $phoneVerification, $message);

        if ($transactionId) {
            $sentVerification = new SentVerification();
            $sentVerification
                ->setPhone($phoneVerification->getPhone())
                ->setSentAt(new \DateTime())
                ->setTransactionId($transactionId)
                ->setMessageSent($message);

            $event->setSentVerification($sentVerification);
            $dispatcher->dispatch(PhoneVerificationEvents::PHONE_VERIFICATION_CODE_SENT, $event);
        }
    }

    private function protectedSendSms(
        SmsService $smsService,
        PhoneVerificationInterface $phoneVerification,
        $message
    ) {
        return $this->breaker->protect(
            function () use ($smsService, $phoneVerification, $message) {
                $transactionId = $smsService->easySend($phoneVerification->getPhone(), $message);

                $phoneUtil = PhoneNumberUtil::getInstance();
                $this->info(
                    'Phone Verification sent to {phone} for user {user_id}. Transaction ID: {transaction_id}',
                    [
                        'user_id' => $phoneVerification->getPerson()->getId(),
                        'phone' => $phoneUtil->format($phoneVerification->getPhone(), PhoneNumberFormat::E164),
                        'transaction_id' => $transactionId,
                    ]
                );

                return $transactionId;
            }
        );
    }

    private function logSmsFailed(
        PersonInterface $person,
        PhoneVerificationInterface $phoneVerification,
        $message,
        $code = null
    ) {
        $phoneUtil = PhoneNumberUtil::getInstance();
        $this->error(
            'Phone Verification NOT sent to {phone} for user {user_id}: [{error_code}] {error_message}',
            [
                'user_id' => $person->getId(),
                'phone' => $phoneUtil->format($phoneVerification->getPhone(), PhoneNumberFormat::E164),
                'error_code' => $code,
                'error_message' => $message,
            ]
        );
    }
}
