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
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\PhoneVerificationBundle\PhoneVerificationEvents;
use LoginCidadao\PhoneVerificationBundle\Service\BlocklistInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class BlocklistSubscriber implements EventSubscriberInterface
{
    /** @var BlocklistInterface */
    private $blocklist;

    /** @var AuthorizationCheckerInterface */
    private $authChecker;

    /**
     * BlocklistSubscriber constructor.
     * @param BlocklistInterface $blocklist
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(BlocklistInterface $blocklist, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->blocklist = $blocklist;
        $this->authChecker = $authorizationChecker;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            PhoneVerificationEvents::PHONE_CHANGED => 'onPhoneChange',
            SecurityEvents::INTERACTIVE_LOGIN => 'onLogin',
        ];
    }

    /**
     * @param PhoneChangedEvent $event
     */
    public function onPhoneChange(PhoneChangedEvent $event)
    {
        $phone = $event->getPerson()->getMobile();
        $this->checkPhone($phone);
    }

    /**
     * @param InteractiveLoginEvent $event
     */
    public function onLogin(InteractiveLoginEvent $event)
    {
        $person = $event->getAuthenticationToken()->getUser();
        if ($person instanceof PersonInterface) {
            $phone = $person->getMobile();
            $this->checkPhone($phone);
        }
    }

    private function checkPhone(?PhoneNumber $phoneNumber)
    {
        if ($phoneNumber instanceof PhoneNumber && $this->authChecker->isGranted('FEATURE_PHONE_BLOCKLIST')) {
            $this->blocklist->checkPhoneNumber($phoneNumber);
        }
    }
}
