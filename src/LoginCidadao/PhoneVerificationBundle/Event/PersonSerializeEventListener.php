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

use JMS\Serializer\EventDispatcher\PreSerializeEvent;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\PhoneVerificationBundle\Service\PhoneVerificationService;

class PersonSerializeEventListener implements EventSubscriberInterface
{
    /** @var PhoneVerificationService */
    protected $phoneVerificationService;

    public function setPhoneVerificationService(PhoneVerificationService $phoneVerificationService)
    {
        $this->phoneVerificationService = $phoneVerificationService;
    }

    public static function getSubscribedEvents()
    {
        return [
            [
                'event' => 'serializer.pre_serialize',
                'method' => 'onPreSerialize',
                'class' => 'LoginCidadao\CoreBundle\Model\PersonInterface',
            ],
        ];
    }

    public function onPreSerialize(PreSerializeEvent $event)
    {
        $person = $event->getObject();
        if (!($person instanceof PersonInterface)) {
            return;
        }

        if ($this->phoneVerificationService) {
            $phoneVerification = $this->phoneVerificationService->getPhoneVerification(
                $person,
                $person->getMobile()
            );
            $person->setPhoneNumberVerified($phoneVerification && $phoneVerification->isVerified());
        }
    }
}
