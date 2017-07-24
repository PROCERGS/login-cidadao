<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\TaskStackBundle\Tests\Service;

use LoginCidadao\TaskStackBundle\Model\IntentTask;
use LoginCidadao\TaskStackBundle\Model\RouteTaskTarget;
use LoginCidadao\TaskStackBundle\Model\UrlTaskTarget;
use LoginCidadao\TaskStackBundle\Service\TaskStackManager;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class TaskStackManagerTest extends \PHPUnit_Framework_TestCase
{
    private function getSession()
    {
        $session = new Session(new MockArraySessionStorage());

        return $session;
    }

    private function getTokenStorage()
    {
        return $this->getMock(
            'Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface'
        );
    }

    private function getRouter()
    {
        $router = $this->getMock('Symfony\Component\Routing\RouterInterface');
        $router->expects($this->any())->method('generate')->willReturnCallback(
            function ($route, $params) {
                return $route;
            }
        );

        return $router;
    }

    private function getDispatcher()
    {
        return $this->getMock(
            'Symfony\Component\EventDispatcher\EventDispatcherInterface'
        );
    }

    private function getStackManager($params = [])
    {
        $session = isset($params['session']) ? $params['session'] : $this->getSession();
        $tokenStorage = isset($params['token_storage']) ? $params['token_storage'] : $this->getTokenStorage();
        $router = isset($params['router']) ? $params['router'] : $this->getRouter();
        $dispatcher = isset($params['dispatcher']) ? $params['dispatcher'] : $this->getDispatcher();

        $manager = new TaskStackManager(
            $session, $tokenStorage, $router, $dispatcher
        );

        return $manager;
    }

    private function getTask($id)
    {
        $task = $this->getMockForAbstractClass('LoginCidadao\TaskStackBundle\Model\AbstractTask');
        $task->expects($this->atLeastOnce())->method('getId')->willReturn($id);

        return $task;
    }

    public function testAddNotSkippedTaskOnce()
    {
        $task = $this->getTask('task1');

        $manager = $this->getStackManager();
        $manager->addNotSkippedTaskOnce($task);
    }

    public function testAddNotSkippedTaskOnceWithIntentTask()
    {
        $task = $this->getTask('task1');
        $intentTask = new IntentTask(new UrlTaskTarget('https://example.com'));

        $manager = $this->getStackManager();
        $manager->addNotSkippedTaskOnce($task, $intentTask);
        $this->assertEquals(2, $manager->countTasks());
    }

    public function testSetTaskSkipped()
    {
        $task = $this->getTask('task1');

        $manager = $this->getStackManager();
        $manager->setTaskSkipped($task);
        $manager->setTaskSkipped($task, false);
    }

    public function testNotAuthenticated()
    {
        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $response = $this->getMock('Symfony\Component\HttpFoundation\Response');

        $tokenStorage = $this->getTokenStorage();
        $tokenStorage->expects($this->once())->method('getToken')->willReturn(null);

        $manager = $this->getStackManager(['token_storage' => $tokenStorage]);
        $manager->processRequest($request, $response);
    }

    public function testEmptyStack()
    {
        $user = $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');

        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token->expects($this->once())->method('getUser')->willReturn($user);

        $tokenStorage = $this->getTokenStorage();
        $tokenStorage->expects($this->once())->method('getToken')->willReturn($token);

        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $request->attributes = new ParameterBag(['_route' => 'foo_bar']);
        $response = $this->getMock('Symfony\Component\HttpFoundation\Response');

        $manager = $this->getStackManager(['token_storage' => $tokenStorage]);
        $manager->emptyStack();
        $manager->processRequest($request, $response);
    }

    public function testPendingRouteTask()
    {
        $user = $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');

        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token->expects($this->once())->method('getUser')->willReturn($user);

        $tokenStorage = $this->getTokenStorage();
        $tokenStorage->expects($this->once())->method('getToken')->willReturn($token);

        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $request->attributes = new ParameterBag(['_route' => 'foo_bar']);
        $response = $this->getMock('Symfony\Component\HttpFoundation\Response');

        $task = $this->getTask('t1');
        $task->expects($this->atLeastOnce())->method('getRoutes')->willReturn([]);
        $task->expects($this->atLeastOnce())->method('getTarget')->willReturn(new RouteTaskTarget('route'));

        $manager = $this->getStackManager(['token_storage' => $tokenStorage]);
        $manager->addNotSkippedTaskOnce($task);
        $manager->processRequest($request, $response);
    }

    public function testCurrentRouteTask()
    {
        $user = $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');

        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token->expects($this->once())->method('getUser')->willReturn($user);

        $tokenStorage = $this->getTokenStorage();
        $tokenStorage->expects($this->once())->method('getToken')->willReturn($token);

        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $request->attributes = new ParameterBag(['_route' => 'foo_bar']);
        $response = $this->getMock('Symfony\Component\HttpFoundation\Response');

        $task = $this->getTask('t1');
        $task->expects($this->atLeastOnce())->method('getRoutes')->willReturn(['foo_bar']);

        $manager = $this->getStackManager(['token_storage' => $tokenStorage]);
        $manager->addNotSkippedTaskOnce($task);
        $manager->processRequest($request, $response);
    }

    public function testPendingTaskInvalidTarget()
    {
        $this->setExpectedException('LoginCidadao\TaskStackBundle\Exception\UnsupportedTargetException');

        $user = $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');

        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token->expects($this->once())->method('getUser')->willReturn($user);

        $tokenStorage = $this->getTokenStorage();
        $tokenStorage->expects($this->once())->method('getToken')->willReturn($token);

        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $request->attributes = new ParameterBag(['_route' => 'foo_bar']);
        $response = $this->getMock('Symfony\Component\HttpFoundation\Response');

        $task = $this->getTask('t1');
        $invalidTarget = $this->getMock('LoginCidadao\TaskStackBundle\Model\TaskTargetInterface');
        $task->expects($this->atLeastOnce())->method('getRoutes')->willReturn([]);
        $task->expects($this->atLeastOnce())->method('getTarget')->willReturn($invalidTarget);

        $manager = $this->getStackManager(['token_storage' => $tokenStorage]);
        $manager->addNotSkippedTaskOnce($task);
        $manager->processRequest($request, $response);
    }

    public function testPendingIntentTask()
    {
        $user = $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');

        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token->expects($this->once())->method('getUser')->willReturn($user);

        $tokenStorage = $this->getTokenStorage();
        $tokenStorage->expects($this->once())->method('getToken')->willReturn($token);

        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $request->attributes = new ParameterBag(['_route' => 'foo_bar']);
        $response = $this->getMock('Symfony\Component\HttpFoundation\Response');

        $task = new IntentTask(new UrlTaskTarget('some_url'));

        $manager = $this->getStackManager(['token_storage' => $tokenStorage]);
        $manager->addNotSkippedTaskOnce($task);
        $manager->processRequest($request, $response);
    }

    public function testSkippedTask()
    {
        $user = $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');

        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token->expects($this->once())->method('getUser')->willReturn($user);

        $tokenStorage = $this->getTokenStorage();
        $tokenStorage->expects($this->once())->method('getToken')->willReturn($token);

        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $request->attributes = new ParameterBag(['_route' => 'foo_bar']);
        $response = $this->getMock('Symfony\Component\HttpFoundation\Response');

        $task = $this->getTask('t1');

        $manager = $this->getStackManager(['token_storage' => $tokenStorage]);
        $manager->addNotSkippedTaskOnce($task);
        $manager->setTaskSkipped($task);
        $manager->processRequest($request, $response);
    }

    public function testGetCurrentTask()
    {
        $task1 = $this->getTask('task1');
        $task2 = $this->getTask('task2');
        $task3 = $this->getTask('task3');

        $manager = $this->getStackManager();
        $manager->addNotSkippedTaskOnce($task1);
        $manager->addNotSkippedTaskOnce($task2);
        $manager->addNotSkippedTaskOnce($task3);

        $this->assertEquals($task3, $manager->getCurrentTask());
    }

    public function testNextTask()
    {
        $task1 = $this->getTask('task1');
        $task2 = $this->getTask('task2');
        $task3 = $this->getTask('task3');

        $manager = $this->getStackManager();
        $this->assertNull($manager->getNextTask());

        $manager->addNotSkippedTaskOnce($task1);
        $manager->addNotSkippedTaskOnce($task2);
        $manager->addNotSkippedTaskOnce($task3);

        $this->assertEquals($task2, $manager->getNextTask());
    }

    public function testCountTasks()
    {
        $task1 = $this->getTask('task1');
        $task2 = $this->getTask('task2');
        $task3 = $this->getTask('task3');

        $manager = $this->getStackManager();
        $manager->addNotSkippedTaskOnce($task1);
        $manager->addNotSkippedTaskOnce($task2);
        $manager->addNotSkippedTaskOnce($task3);

        $this->assertEquals(3, $manager->countTasks());
    }

    public function testOAuthToken()
    {
        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $response = $this->getMock('Symfony\Component\HttpFoundation\Response');

        $token = $this->getMock('FOS\OAuthServerBundle\Security\Authentication\Token\OAuthToken');

        $tokenStorage = $this->getTokenStorage();
        $tokenStorage->expects($this->once())->method('getToken')->willReturn($token);

        $manager = $this->getStackManager(['token_storage' => $tokenStorage]);
        $actual = $manager->processRequest($request, $response);

        $this->assertEquals($response, $actual);
    }

    public function testHasIntentTask()
    {
        $intentTask = new IntentTask(new UrlTaskTarget('https://example.com'));
        $manager = $this->getStackManager();

        $this->assertFalse($manager->hasIntentTask());

        $manager->addNotSkippedTaskOnce($intentTask);

        $this->assertTrue($manager->hasIntentTask());
    }
}
