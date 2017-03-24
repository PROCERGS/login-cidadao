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


use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\PhoneVerificationBundle\PhoneVerificationEvents;
use LoginCidadao\PhoneVerificationBundle\Service\AbstractPhoneVerificationService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PhoneVerificationSubscriber implements EventSubscriberInterface
{
    /** @var AbstractPhoneVerificationService */
    private $phoneVerificationService;

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
            PhoneVerificationEvents::PHONE_VERIFICATION_REQUESTED => 'onCodeRequest',
            PhoneVerificationEvents::PHONE_VERIFIED => 'onVerify',
        ];
    }

    public function onPhoneChange(PhoneChangedEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        $person = $event->getPerson();

        if (!($person instanceof PersonInterface)) {
            return;
        }

        $oldPhoneVerification = $this->phoneVerificationService->getPhoneVerification($person, $person->getMobile());
        if ($oldPhoneVerification) {
            $this->phoneVerificationService->removePhoneVerification($oldPhoneVerification);
        }

        $dispatcher->dispatch(PhoneVerificationEvents::PHONE_VERIFICATION_REQUESTED, $event);
    }
}
