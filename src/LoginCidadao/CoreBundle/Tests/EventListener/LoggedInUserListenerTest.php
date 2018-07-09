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

use LoginCidadao\CoreBundle\Entity\Person;
use LoginCidadao\CoreBundle\EventListener\LoggedInUserListener;
use LoginCidadao\CoreBundle\Helper\SecurityHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

class LoggedInUserListenerTest extends TestCase
{
    public function testOnKernelRequest()
    {
        $person = new Person();

        /** @var SecurityHelper|\PHPUnit_Framework_MockObject_MockObject $securityHelper */
        $securityHelper = $this->getMockBuilder('LoginCidadao\CoreBundle\Helper\SecurityHelper')
            ->disableOriginalConstructor()->getMock();
        $securityHelper->expects($this->exactly(2))->method('getUser')->willReturn($person);
        $securityHelper->expects($this->once())->method('hasToken')->willReturn(true);
        $securityHelper->expects($this->once())->method('isOAuthToken')->willReturn(false);
        $securityHelper->expects($this->once())
            ->method('isGranted')->with('IS_AUTHENTICATED_REMEMBERED')->willReturn(true);

        /** @var RouterInterface|\PHPUnit_Framework_MockObject_MockObject $router */
        $router = $this->createMock('Symfony\Component\Routing\RouterInterface');
        $router->expects($this->once())->method('generate')->with('lc_resend_confirmation_email');

        $flashBag = $this->createMock('Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface');
        $flashBag->expects($this->once())
            ->method('add')->with('alert.unconfirmed.email', $this->isType('string'));

        /** @var Session|\PHPUnit_Framework_MockObject_MockObject $session */
        $session = $this->getMockBuilder('Symfony\Component\HttpFoundation\Session\Session')
            ->disableOriginalConstructor()->getMock();
        $session->expects($this->once())->method('getFlashBag')->willReturn($flashBag);

        /** @var TranslatorInterface|\PHPUnit_Framework_MockObject_MockObject $translator */
        $translator = $this->createMock('Symfony\Component\Translation\TranslatorInterface');
        $translator->expects($this->exactly(2))->method('trans')->with($this->logicalOr(
            $this->equalTo('notification.unconfirmed.email.title'),
            $this->equalTo('notification.unconfirmed.email.shortText')
        ));

        /** @var GetResponseEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()->getMock();
        $event->expects($this->once())->method('isMasterRequest')->willReturn(true);

        $listener = new LoggedInUserListener($securityHelper, $router, $session, $translator, false);
        $listener->onKernelRequest($event);
    }
}
