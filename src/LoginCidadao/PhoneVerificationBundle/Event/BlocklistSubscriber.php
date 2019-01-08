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

use libphonenumber\PhoneNumber;
use LoginCidadao\CoreBundle\Security\User\Manager\UserManager;
use LoginCidadao\PhoneVerificationBundle\PhoneVerificationEvents;
use LoginCidadao\PhoneVerificationBundle\Service\BlocklistInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BlocklistSubscriber implements EventSubscriberInterface
{
    /** @var BlocklistInterface */
    private $blocklist;

    /** @var UserManager */
    private $userManager;

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            PhoneVerificationEvents::PHONE_CHANGED => 'onPhoneChange',
        ];
    }

    public function onPhoneChange(PhoneChangedEvent $event)
    {
        $phone = $event->getPerson()->getMobile();
        if ($phone instanceof PhoneNumber && $this->blocklist->isBlocked($phone)) {
            $this->userManager->blockUsersByPhone($phone);
        }
    }
}
