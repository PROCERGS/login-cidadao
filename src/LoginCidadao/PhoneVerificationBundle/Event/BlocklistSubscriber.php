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
use LoginCidadao\CoreBundle\Security\User\Manager\UserManager;
use LoginCidadao\PhoneVerificationBundle\PhoneVerificationEvents;
use LoginCidadao\PhoneVerificationBundle\Service\BlocklistInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class BlocklistSubscriber implements EventSubscriberInterface
{
    /** @var BlocklistInterface */
    private $blocklist;

    /** @var UserManager */
    private $userManager;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * BlocklistSubscriber constructor.
     * @param BlocklistInterface $blocklist
     * @param UserManager $userManager
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        BlocklistInterface $blocklist,
        UserManager $userManager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->blocklist = $blocklist;
        $this->userManager = $userManager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            PhoneVerificationEvents::PHONE_CHANGED => 'onPhoneChange',
            SecurityEvents::INTERACTIVE_LOGIN => 'onLogin',
            KernelEvents::REQUEST => 'onRequest', // TODO: remove
        ];
    }

    public function onPhoneChange(PhoneChangedEvent $event)
    {
        $phone = $event->getPerson()->getMobile();
        $this->checkPhone($phone);
    }

    public function onLogin(InteractiveLoginEvent $event)
    {
        try {
            $person = $event->getAuthenticationToken()->getUser();
            $phone = $person->getMobile();
        } catch (\Exception $e) {
            // User object not available...
            return;
        }

        $this->checkPhone($phone);
    }

    public function onRequest(GetResponseEvent $event)
    {
        if ($event->isMasterRequest() && null !== $this->tokenStorage->getToken()) {
            /** @var PersonInterface $person */
            $person = $this->tokenStorage->getToken()->getUser();
            if ($person instanceof PersonInterface) {
                $phone = $person->getMobile();

                $this->checkPhone($phone);
            }
        }
    }

    /**
     * @param PhoneNumber|null $phoneNumber
     */
    private function checkPhone(?PhoneNumber $phoneNumber)
    {
        if ($phoneNumber instanceof PhoneNumber) {
            $this->blocklist->checkPhoneNumber($phoneNumber);
        }
    }
}
