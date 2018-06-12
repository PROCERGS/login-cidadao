<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Tests\EventListener;

use LoginCidadao\CoreBundle\Entity\Person;
use LoginCidadao\CoreBundle\Helper\SecurityHelper;
use LoginCidadao\OAuthBundle\Entity\Client;
use LoginCidadao\OpenIDBundle\EventListener\TaskSubscriber;
use LoginCidadao\OpenIDBundle\Manager\ClientManager;
use LoginCidadao\OpenIDBundle\Task\CompleteUserInfoTaskValidator;
use LoginCidadao\TaskStackBundle\Event\GetTasksEvent;
use LoginCidadao\TaskStackBundle\TaskStackEvents;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class TaskSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents()
    {
        $this->assertSame([
            TaskStackEvents::GET_TASKS => ['onGetTasks', 50],
        ], TaskSubscriber::getSubscribedEvents());
    }

    public function testOnGetTasksNoPersonOrClient()
    {
        $securityHelper = $this->getSecurityHelper();
        $clientManager = $this->getClientManager();
        $taskValidator = $this->getCompleteUserInfoTaskValidator();

        $request = $this->getRequest();
        $this->requestExpectsClientId($request);

        $event = $this->getEvent();
        $event->expects($this->once())
            ->method('getRequest')->willReturn($request);

        $subscriber = new TaskSubscriber($securityHelper, $clientManager, $taskValidator);
        $subscriber->onGetTasks($event);
    }

    public function testOnGetTasksWithPersonAndClient()
    {
        $client = new Client();
        $person = new Person();

        $task = $this->getMockBuilder('LoginCidadao\OpenIDBundle\Task\CompleteUserInfoTask')
            ->disableOriginalConstructor()->getMock();

        $securityHelper = $this->getSecurityHelper();
        $securityHelper->expects($this->once())
            ->method('getUser')->willReturn($person);

        $clientManager = $this->getClientManager();
        $clientManager->expects($this->once())
            ->method('getClientById')->with('client_id')
            ->willReturn($client);

        $request = $this->getRequest();
        $this->requestExpectsClientId($request);

        $taskValidator = $this->getCompleteUserInfoTaskValidator();
        $taskValidator->expects($this->once())
            ->method('getCompleteUserInfoTask')->with($person, $client, $request)
            ->willReturn($task);

        $event = $this->getEvent();
        $event->expects($this->once())
            ->method('getRequest')->willReturn($request);
        $event->expects($this->once())
            ->method('addTask')->with($task);

        $subscriber = new TaskSubscriber($securityHelper, $clientManager, $taskValidator);
        $subscriber->onGetTasks($event);
    }

    /**
     * @return SecurityHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getSecurityHelper()
    {
        return $this->getMockBuilder('LoginCidadao\CoreBundle\Helper\SecurityHelper')
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * @return ClientManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getClientManager()
    {
        return $this->getMockBuilder('LoginCidadao\OpenIDBundle\Manager\ClientManager')
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * @return CompleteUserInfoTaskValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getCompleteUserInfoTaskValidator()
    {
        return $this->getMockBuilder('LoginCidadao\OpenIDBundle\Task\CompleteUserInfoTaskValidator')
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * @return Request|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getRequest()
    {
        return $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()->getMock();
    }

    private function requestExpectsClientId(\PHPUnit_Framework_MockObject_MockObject $request)
    {
        $params = $this->logicalOr($this->equalTo('client_id'), $this->equalTo('clientId'));
        $request->expects($this->exactly(2))
            ->method('get')->with($params)
            ->willReturn('client_id');
    }

    /**
     * @return GetTasksEvent|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getEvent()
    {
        return $this->getMockBuilder('LoginCidadao\TaskStackBundle\Event\GetTasksEvent')
            ->disableOriginalConstructor()->getMock();
    }
}
