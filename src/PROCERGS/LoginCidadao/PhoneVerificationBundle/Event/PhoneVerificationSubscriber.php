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

use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use LoginCidadao\PhoneVerificationBundle\Event\PhoneChangedEvent;
use LoginCidadao\PhoneVerificationBundle\PhoneVerificationEvents;
use PROCERGS\Sms\SmsService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Translation\TranslatorInterface;

class PhoneVerificationSubscriber implements EventSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait, LoggerTrait;

    /** @var SmsService */
    private $smsService;

    /** @var TranslatorInterface */
    private $translator;

    /**
     * PhoneVerificationSubscriber constructor.
     *
     * @param SmsService $smsService
     * @param TranslatorInterface $translator
     */
    public function __construct(SmsService $smsService, TranslatorInterface $translator)
    {
        $this->smsService = $smsService;
        $this->translator = $translator;
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

    public function onVerificationRequest(PhoneChangedEvent $event)
    {
        $person = $event->getPerson();
        $phoneUtil = PhoneNumberUtil::getInstance();

        $message = $this->translator->trans('phone_verification.sms.message', ['%code%' => 666]);
        $transactionId = $this->smsService->easySend($person->getMobile(), $message);

        $this->info(
            'Phone Verification sent to {phone} for user {user_id}. Transaction ID: {transaction_id}',
            [
                'user_id' => $person->getId(),
                'phone' => $phoneUtil->format($person->getMobile(), PhoneNumberFormat::E164),
                'transaction_id' => $transactionId,
            ]
        );
    }
}
