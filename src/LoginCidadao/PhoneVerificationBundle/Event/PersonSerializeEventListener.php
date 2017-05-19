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
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\PhoneVerificationBundle\Service\PhoneVerificationService;
use LoginCidadao\PhoneVerificationBundle\Service\PhoneVerificationServiceInterface;

class PersonSerializeEventListener
{
    /** @var PhoneVerificationService */
    protected $phoneVerificationService;

    /**
     * PersonSerializeEventListener constructor.
     * @param PhoneVerificationService|PhoneVerificationServiceInterface $phoneVerificationService
     */
    public function __construct(PhoneVerificationServiceInterface $phoneVerificationService)
    {
        $this->phoneVerificationService = $phoneVerificationService;
    }

    public function onPreSerialize(PreSerializeEvent $event)
    {
        $person = $event->getObject();
        if (!$person instanceof PersonInterface || !$person->getMobile()) {
            return;
        }

        $phoneVerification = $this->phoneVerificationService->getPhoneVerification($person, $person->getMobile());
        $person->setPhoneNumberVerified($phoneVerification && $phoneVerification->isVerified());
    }
}
