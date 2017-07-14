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

use LoginCidadao\TaskStackBundle\Event\GetTasksEvent;

class GetTasksEventTest extends \PHPUnit_Framework_TestCase
{
    public function testEvent()
    {
        $task = $this->getMock('LoginCidadao\TaskStackBundle\Model\TaskInterface');

        $stackManager = $this->getMock('LoginCidadao\TaskStackBundle\Service\TaskStackManagerInterface');
        $stackManager->expects($this->once())->method('addNotSkippedTaskOnce')->with($task);
        $stackManager->expects($this->exactly(2))->method('setTaskSkipped')->with($task);

        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $event = new GetTasksEvent($stackManager, $request);

        $event->forceAddUniqueTask($task);
        $event->setTaskSkipped($task);

        $this->assertEquals($request, $event->getRequest());
    }

    public function testAddTaskIfStackEmpty()
    {
        $tasks = [];
        $task = $this->getMock('LoginCidadao\TaskStackBundle\Model\TaskInterface');

        $stackManager = $this->getMock('LoginCidadao\TaskStackBundle\Service\TaskStackManagerInterface');
        $stackManager->expects($this->atLeastOnce())->method('addNotSkippedTaskOnce')
            ->with($task)->willReturnCallback(
                function ($task) use (&$tasks) {
                    foreach ($tasks as $t) {
                        if ($t == $task) {
                            return $this;
                        }
                    }
                    $tasks[] = $task;

                    return $this;
                }
            );
        $stackManager->expects($this->atLeastOnce())->method('countTasks')->willReturnCallback(
            function () use ($tasks) {
                return count($tasks);
            }
        );

        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $event = new GetTasksEvent($stackManager, $request);

        $event->addTaskIfStackEmpty($task);
        $event->addTaskIfStackEmpty($task);
        $this->assertEquals(1, count($tasks));
    }

    public function testAddTaskIfStackEmptyWithIntent()
    {
        $tasks = [];
        $task = $this->getMock('LoginCidadao\TaskStackBundle\Model\TaskInterface');
        $intentTask = $this->getMockBuilder('LoginCidadao\TaskStackBundle\Model\IntentTask')
            ->disableOriginalConstructor()
            ->getMock();

        $stackManager = $this->getMock('LoginCidadao\TaskStackBundle\Service\TaskStackManagerInterface');
        $stackManager->expects($this->atLeastOnce())->method('getCurrentTask')->willReturnCallback(
            function () use (&$tasks) {
                return $tasks[0];
            }
        );
        $stackManager->expects($this->atLeastOnce())->method('addNotSkippedTaskOnce')
            ->with($this->isInstanceOf('LoginCidadao\TaskStackBundle\Model\TaskInterface'))
            ->willReturnCallback(
                function ($task) use (&$tasks) {
                    foreach ($tasks as $t) {
                        if ($t == $task) {
                            return $this;
                        }
                    }
                    $tasks[] = $task;

                    return $this;
                }
            );
        $stackManager->expects($this->atLeastOnce())->method('countTasks')->willReturnCallback(
            function () use (&$tasks) {
                return count($tasks);
            }
        );

        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $event = new GetTasksEvent($stackManager, $request);

        $event->addTaskIfStackEmpty($intentTask);
        $this->assertEquals(1, count($tasks));

        $event->addTaskIfStackEmpty($task);
        $event->addTaskIfStackEmpty($task);
        $this->assertEquals(2, count($tasks));
    }
}
