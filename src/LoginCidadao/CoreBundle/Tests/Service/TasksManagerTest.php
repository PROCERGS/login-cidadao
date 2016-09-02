<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Tests\Service;

use LoginCidadao\CoreBundle\Service\TasksManager;

class TasksManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var TasksManager */
    private $tasksManager;

    public function setUp()
    {
        $this->tasksManager = new TasksManager();
    }

    public function testNextTaskPriority()
    {
        $tasks = [];

        $taskBuilder = new TestTaskBuilder();

        $tasks[] = $taskBuilder->setPriority(10)
            ->setMandatory(false)
            ->setName('Task 1')
            ->setTarget(['', ['']])
            ->setTaskRoutes(['a', 'b'])
            ->build();
        $tasks[] = $taskBuilder->setPriority(50)
            ->setMandatory(false)
            ->setName('Task 2')
            ->setTarget(['', ['']])
            ->setTaskRoutes(['a', 'b'])
            ->build();
        $tasks[] = $taskBuilder->setPriority(70)
            ->setMandatory(false)
            ->setName('Task 3')
            ->setTarget(['', ['']])
            ->setTaskRoutes(['a', 'b'])
            ->build();

        $task = $this->tasksManager->getNextTask($tasks);

        $this->assertEquals(70, $task->getPriority());
    }

    public function testMandatory()
    {
        $tasks = [];

        $taskBuilder = new TestTaskBuilder();

        $tasks[] = $taskBuilder->setPriority(10)
            ->setMandatory(true)
            ->setName('Task 1')
            ->setTarget(['', ['']])
            ->setTaskRoutes(['a', 'b'])
            ->build();
        $tasks[] = $taskBuilder->setPriority(50)
            ->setMandatory(false)
            ->setName('Task 2')
            ->setTarget(['', ['']])
            ->setTaskRoutes(['a', 'b'])
            ->build();
        $tasks[] = $taskBuilder->setPriority(70)
            ->setMandatory(false)
            ->setName('Task 3')
            ->setTarget(['', ['']])
            ->setTaskRoutes(['a', 'b'])
            ->setSkipRoute('skip')
            ->build();

        $task = $this->tasksManager->getNextTask($tasks);

        $this->assertEquals(10, $task->getPriority());
    }

    public function testSkip()
    {
        $tasks = [];

        $taskBuilder = new TestTaskBuilder();

        $tasks[] = $taskBuilder->setPriority(10)
            ->setMandatory(false)
            ->setName('Task 1')
            ->setTarget(['', ['']])
            ->setTaskRoutes(['a', 'b'])
            ->build();
        $tasks[] = $taskBuilder->setPriority(50)
            ->setMandatory(false)
            ->setName('Task 2')
            ->setTarget(['', ['']])
            ->setTaskRoutes(['a', 'b'])
            ->build();
        $tasks[] = $taskBuilder->setPriority(70)
            ->setMandatory(false)
            ->setName('Task 3')
            ->setTarget(['', ['']])
            ->setTaskRoutes(['a', 'b'])
            ->setSkipRoute('skip')
            ->build();

        $task = $this->tasksManager->getNextTask($tasks, 'skip');

        $this->assertEquals(50, $task->getPriority());
    }
}
