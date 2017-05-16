<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\PhoneVerificationBundle\Event;


use FOS\UserBundle\FOSUserEvents;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\PhoneVerificationBundle\PhoneVerificationEvents;
use LoginCidadao\PhoneVerificationBundle\Service\PhoneVerificationServiceInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class PhoneVerificationSubscriber implements EventSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait, LoggerTrait;

    /** @var PhoneVerificationServiceInterface */
    private $phoneVerificationService;

    /**
     * PhoneVerificationSubscriber constructor.
     * @param PhoneVerificationServiceInterface $phoneVerificationService
     */
    public function __construct(PhoneVerificationServiceInterface $phoneVerificationService)
    {
        $this->phoneVerificationService = $phoneVerificationService;
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
            PhoneVerificationEvents::PHONE_CHANGED => 'onPhoneChange',
            PhoneVerificationEvents::PHONE_VERIFICATION_REQUESTED => 'onVerificationRequest',
            PhoneVerificationEvents::PHONE_VERIFICATION_CODE_SENT => 'onCodeSent',
            SecurityEvents::INTERACTIVE_LOGIN => 'onLogin',
        ];
    }

    public function onPhoneChange(PhoneChangedEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        $person = $event->getPerson();
        $oldPhone = $event->getOldPhone();
        $newPhone = $person->getMobile();

        $phoneUtil = PhoneNumberUtil::getInstance();
        $this->info(
            'Phone changed from {old} to {new} for user {id}',
            [
                'id' => $person->getId(),
                'old' => $oldPhone ? $phoneUtil->format($oldPhone, PhoneNumberFormat::E164) : null,
                'new' => $newPhone ? $phoneUtil->format($newPhone, PhoneNumberFormat::E164) : null,
            ]
        );

        if ($oldPhone) {
            $oldPhoneVerification = $this->phoneVerificationService->getPhoneVerification($person, $oldPhone);
        } else {
            $oldPhoneVerification = null;
        }

        if ($oldPhoneVerification) {
            $this->phoneVerificationService->removePhoneVerification($oldPhoneVerification);
        }

        if ($newPhone) {
            $phoneVerification = $this->phoneVerificationService->createPhoneVerification(
                $person,
                $newPhone
            );

            $sendEvent = new SendPhoneVerificationEvent($phoneVerification);
            $dispatcher->dispatch(PhoneVerificationEvents::PHONE_VERIFICATION_REQUESTED, $sendEvent);
        }
    }

    public function onVerificationRequest(SendPhoneVerificationEvent $event)
    {
        $person = $event->getPhoneVerification()->getPerson();
        $phoneUtil = PhoneNumberUtil::getInstance();
        $this->info(
            'Phone Verification requested for {phone} for user {user_id}',
            [
                'user_id' => $person->getId(),
                'phone' => $phoneUtil->format($person->getMobile(), PhoneNumberFormat::E164),
            ]
        );
    }

    public function onCodeSent(SendPhoneVerificationEvent $event)
    {
        $sentVerification = $event->getSentVerification();
        $this->phoneVerificationService->registerVerificationSent($sentVerification);
    }

    public function onLogin(InteractiveLoginEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        /** @var PersonInterface $person */
        $person = $event->getAuthenticationToken()->getUser();
        if (!$person instanceof PersonInterface || !$phone = $person->getMobile()) {
            return;
        }

        $verification = $this->phoneVerificationService->getPhoneVerification($person, $phone);
        if (!$verification) {
            $this->info(
                "User {user_id} has a phone but didn't verify it. Creating Phone Verification request.",
                [
                    'user_id' => $person->getId(),
                ]
            );
            $verification = $this->phoneVerificationService->enforcePhoneVerification($person, $phone);

            $sendEvent = new SendPhoneVerificationEvent($verification);
            $dispatcher->dispatch(PhoneVerificationEvents::PHONE_VERIFICATION_REQUESTED, $sendEvent);
        }
    }
}
