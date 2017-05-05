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

use FOS\UserBundle\FOSUserEvents;
use libphonenumber\PhoneNumberUtil;
use LoginCidadao\PhoneVerificationBundle\Event\ProfileEditSubscriber;

class ProfileEditSubscriberTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSubscribedEvents()
    {
        $this->assertArrayHasKey(
            FOSUserEvents::PROFILE_EDIT_INITIALIZE,
            ProfileEditSubscriber::getSubscribedEvents()
        );
        $this->assertArrayHasKey(
            FOSUserEvents::PROFILE_EDIT_SUCCESS,
            ProfileEditSubscriber::getSubscribedEvents()
        );
    }

    public function testOnProfileEditInitialize()
    {
        $person = $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');
        $person->expects($this->once())->method('getMobile');

        $event = $this->getMockBuilder('FOS\UserBundle\Event\GetResponseUserEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())->method('getUser')->willReturn($person);

        $subscriber = new ProfileEditSubscriber();
        $subscriber->onProfileEditInitialize($event);
    }

    public function testSkipOnProfileEditInitialize()
    {
        $event = $this->getMockBuilder('FOS\UserBundle\Event\GetResponseUserEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())->method('getUser')->willReturn(null);

        $subscriber = new ProfileEditSubscriber();
        $subscriber->onProfileEditInitialize($event);
    }

    public function testOnProfileEditSuccess()
    {
        $person = $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');
        $person->expects($this->any())->method('getMobile')
            ->willReturn(PhoneNumberUtil::getInstance()->parse('+5551999999999', 'BR'));

        $form = $this->getMockBuilder('Symfony\Component\Form\FormInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $form->expects($this->once())->method('getData')->willReturn($person);

        $event = $this->getMockBuilder('FOS\UserBundle\Event\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())->method('getForm')->willReturn($form);

        $dispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $subscriber = new ProfileEditSubscriber();

        // Prepare oldPhone
        $person2 = $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');
        $person2->expects($this->any())->method('getMobile')
            ->willReturn(PhoneNumberUtil::getInstance()->parse('+5551999998888', 'BR'));

        $event1 = $this->getMockBuilder('FOS\UserBundle\Event\GetResponseUserEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event1->expects($this->once())->method('getUser')->willReturn($person2);
        $subscriber->onProfileEditInitialize($event1);
        // ----------------

        $subscriber->onProfileEditSuccess($event, FOSUserEvents::PROFILE_EDIT_SUCCESS, $dispatcher);
    }

    public function testSkipOnProfileEditSuccess()
    {
        $form = $this->getMockBuilder('Symfony\Component\Form\FormInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $form->expects($this->once())->method('getData')->willReturn(null);

        $event = $this->getMockBuilder('FOS\UserBundle\Event\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())->method('getForm')->willReturn($form);

        $dispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $subscriber = new ProfileEditSubscriber();
        $subscriber->onProfileEditSuccess($event, FOSUserEvents::PROFILE_EDIT_SUCCESS, $dispatcher);
    }
}
