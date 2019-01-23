<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\PhoneVerificationBundle\Tests\Event;

use libphonenumber\PhoneNumber;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\PhoneVerificationBundle\Event\BlocklistSubscriber;
use LoginCidadao\PhoneVerificationBundle\Event\PhoneChangedEvent;
use LoginCidadao\PhoneVerificationBundle\PhoneVerificationEvents;
use LoginCidadao\PhoneVerificationBundle\Service\BlocklistInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class BlocklistSubscriberTest extends TestCase
{
    public function testSubscribedEvents()
    {
        $this->assertEquals([
            PhoneVerificationEvents::PHONE_CHANGED => 'onPhoneChange',
            SecurityEvents::INTERACTIVE_LOGIN => 'onLogin',
        ], BlocklistSubscriber::getSubscribedEvents());
    }

    public function testOnPhoneChange()
    {
        $phoneNumber = $this->createMock(PhoneNumber::class);

        $person = $this->createMock(PersonInterface::class);
        $person->expects($this->once())->method('getMobile')->willReturn($phoneNumber);

        /** @var PhoneChangedEvent|MockObject $event */
        $event = $this->createMock(PhoneChangedEvent::class);
        $event->expects($this->once())->method('getPerson')->willReturn($person);

        $blocklistService = $this->getBlocklistService(true, $phoneNumber);

        $subscriber = new BlocklistSubscriber($blocklistService, $this->getAuthorizationChecker());
        $subscriber->onPhoneChange($event);
    }

    public function testOnLogin()
    {
        $phoneNumber = $this->createMock(PhoneNumber::class);

        $person = $this->createMock(PersonInterface::class);
        $person->expects($this->once())->method('getMobile')->willReturn($phoneNumber);

        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->once())->method('getUser')->willReturn($person);

        /** @var InteractiveLoginEvent|MockObject $event */
        $event = $this->createMock(InteractiveLoginEvent::class);
        $event->expects($this->once())->method('getAuthenticationToken')->willReturn($token);

        $blocklistService = $this->getBlocklistService(true, $phoneNumber);

        $subscriber = new BlocklistSubscriber($blocklistService, $this->getAuthorizationChecker());
        $subscriber->onLogin($event);
    }

    public function testDisabledFeatureOnPhoneChange()
    {
        $phoneNumber = $this->createMock(PhoneNumber::class);

        $person = $this->createMock(PersonInterface::class);
        $person->expects($this->once())->method('getMobile')->willReturn($phoneNumber);

        /** @var PhoneChangedEvent|MockObject $event */
        $event = $this->createMock(PhoneChangedEvent::class);
        $event->expects($this->once())->method('getPerson')->willReturn($person);

        $blocklistService = $this->getBlocklistService(false);
        $blocklistService->expects($this->never())->method('checkPhoneNumber');

        $subscriber = new BlocklistSubscriber($blocklistService, $this->getAuthorizationChecker(false));
        $subscriber->onPhoneChange($event);
    }

    public function testDisabledFeatureOnLogin()
    {
        $phoneNumber = $this->createMock(PhoneNumber::class);

        $person = $this->createMock(PersonInterface::class);
        $person->expects($this->once())->method('getMobile')->willReturn($phoneNumber);

        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->once())->method('getUser')->willReturn($person);

        /** @var InteractiveLoginEvent|MockObject $event */
        $event = $this->createMock(InteractiveLoginEvent::class);
        $event->expects($this->once())->method('getAuthenticationToken')->willReturn($token);

        $blocklistService = $this->getBlocklistService(false);

        $subscriber = new BlocklistSubscriber($blocklistService, $this->getAuthorizationChecker(false));
        $subscriber->onLogin($event);
    }

    /**
     * @param bool $expectCheck
     * @param PhoneNumber|null $phoneNumber
     * @return MockObject|BlocklistInterface
     */
    private function getBlocklistService(bool $expectCheck = false, PhoneNumber $phoneNumber = null)
    {
        $blocklistService = $this->createMock(BlocklistInterface::class);

        if ($expectCheck) {
            $blocklistService->expects($this->once())->method('checkPhoneNumber')->with($phoneNumber);
        }

        return $blocklistService;
    }

    /**
     * @param bool $isGranted
     * @return MockObject|AuthorizationCheckerInterface
     */
    private function getAuthorizationChecker(bool $isGranted = true)
    {
        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker->expects($this->once())->method('isGranted')
            ->with('FEATURE_PHONE_BLOCKLIST')->willReturn($isGranted);

        return $authorizationChecker;
    }
}
