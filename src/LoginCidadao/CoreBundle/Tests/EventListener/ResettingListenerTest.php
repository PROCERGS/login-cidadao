<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Tests\EventListener;

use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseNullableUserEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserInterface;
use LoginCidadao\CoreBundle\EventListener\ResettingListener;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ResettingListenerTest extends TestCase
{
    public function testGetSubscribedEvents()
    {
        $this->assertEquals([
            FOSUserEvents::RESETTING_RESET_INITIALIZE => 'onResettingResetInitialize',
            FOSUserEvents::RESETTING_RESET_SUCCESS => 'onResettingResetSuccess',
            FOSUserEvents::RESETTING_SEND_EMAIL_INITIALIZE => 'onResettingEmailRequested',
        ], ResettingListener::getSubscribedEvents());
    }

    public function testOnResettingResetInitialize()
    {
        $tokenTtl = 300;

        $user = $this->createMock(PersonInterface::class);
        $user->expects($this->once())->method('isPasswordRequestNonExpired')
            ->with($tokenTtl)->willReturn(false);

        /** @var GetResponseUserEvent|MockObject $event */
        $event = $this->createMock(GetResponseUserEvent::class);
        $event->expects($this->once())->method('getUser')->willReturn($user);
        $event->expects($this->once())->method('setResponse')
            ->willReturnCallback(function (RedirectResponse $response) {
                $this->assertEquals('fos_user_resetting_request', $response->getTargetUrl());
            });

        $subscriber = new ResettingListener($this->getRouter(), $tokenTtl);
        $subscriber->onResettingResetInitialize($event);
    }

    public function testOnResettingResetSuccess()
    {
        $user = $this->createMock(UserInterface::class);
        $user->expects($this->once())->method('setConfirmationToken')->with(null);
        $user->expects($this->once())->method('setPasswordRequestedAt')->with(null);
        $user->expects($this->once())->method('setEnabled')->with(true);

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())->method('getData')->willReturn($user);

        /** @var FormEvent|MockObject $event */
        $event = $this->createMock(FormEvent::class);
        $event->expects($this->once())->method('getForm')->willReturn($form);
        $event->expects($this->once())->method('setResponse')
            ->willReturnCallback(function (RedirectResponse $response) {
                $this->assertEquals('fos_user_profile_edit', $response->getTargetUrl());
            });

        $subscriber = new ResettingListener($this->getRouter(), 300);
        $subscriber->onResettingResetSuccess($event);
    }

    public function testOnResettingEmailRequested()
    {
        /** @var GetResponseNullableUserEvent|MockObject $event */
        $event = $this->createMock(GetResponseNullableUserEvent::class);
        $event->expects($this->once())->method('getUser')->willReturn(null);
        $event->expects($this->once())->method('setResponse')
            ->willReturnCallback(function (RedirectResponse $response) {
                $this->assertEquals('lc_resetting_user_not_found', $response->getTargetUrl());
            });

        $subscriber = new ResettingListener($this->getRouter(), 300);
        $subscriber->onResettingEmailRequested($event);
    }

    /**
     * @return MockObject|UrlGeneratorInterface
     */
    private function getRouter()
    {
        $router = $this->createMock(UrlGeneratorInterface::class);
        $router->expects($this->once())->method('generate')->willReturnCallback(function ($route) {
            return $route;
        });

        return $router;
    }
}
