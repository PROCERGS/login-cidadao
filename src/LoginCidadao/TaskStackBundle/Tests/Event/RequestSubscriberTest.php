<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\TaskStackBundle\Tests\Event;

use LoginCidadao\CoreBundle\Event\LoginCidadaoCoreEvents;
use LoginCidadao\TaskStackBundle\Event\RequestSubscriber;
use LoginCidadao\TaskStackBundle\Service\TaskStackManagerInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|TaskStackManagerInterface
     */
    private function getStackManager()
    {
        return $this->getMock('LoginCidadao\TaskStackBundle\Service\TaskStackManagerInterface');
    }

    public function testGetSubscribedEvents()
    {
        $expected = [
            KernelEvents::REQUEST => 'onRequest',
            LoginCidadaoCoreEvents::AUTHENTICATION_ENTRY_POINT_START => 'onAuthenticationStart',
        ];
        $this->assertEquals($expected, RequestSubscriber::getSubscribedEvents());
    }

    public function testOnRequest()
    {
        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $response = $this->getMock('Symfony\Component\HttpFoundation\Response');

        $stackManager = $this->getStackManager();
        $stackManager->expects($this->once())->method('processRequest')->willReturn($response);

        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())->method('isMasterRequest')->willReturn(true);
        $event->expects($this->once())->method('getRequest')->willReturn($request);
        $event->expects($this->once())->method('getResponse')->willReturn($response);
        $event->expects($this->once())->method('setResponse')->with($response);

        $subscriber = new RequestSubscriber($stackManager);
        $subscriber->onRequest($event);
    }

    public function testOnRequestSkipNonMaster()
    {
        $stackManager = $this->getStackManager();

        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())->method('isMasterRequest')->willReturn(false);

        $subscriber = new RequestSubscriber($stackManager);
        $subscriber->onRequest($event);
    }

    public function testOnAuthenticationStart()
    {
        $stackManager = $this->getStackManager();
        $stackManager->expects($this->once())->method('emptyStack');
        $stackManager->expects($this->once())->method('addNotSkippedTaskOnce');

        $session = $this->getMock('Symfony\Component\HttpFoundation\Session\SessionInterface');
        $session->expects($this->once())->method('has')->willReturn(true);
        $session->expects($this->once())->method('remove');

        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $request->expects($this->once())->method('getSession')->willReturn($session);

        $event = $this->getMockBuilder('LoginCidadao\TaskStackBundle\Event\EntryPointStartEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())->method('getRequest')->willReturn($request);

        $subscriber = new RequestSubscriber($stackManager);
        $subscriber->onAuthenticationStart($event);
    }
}
