<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\TaskStackBundle\Tests\Model;

use LoginCidadao\TaskStackBundle\Model\TaskStack;
use PHPUnit\Framework\TestCase;

class TaskStackTest extends TestCase
{
    public function testTaskStack()
    {
        $task1 = $this->createMock('LoginCidadao\TaskStackBundle\Model\TaskInterface');
        $task1->expects($this->any())->method('getId')->willReturn('task1');

        $task2 = $this->createMock('LoginCidadao\TaskStackBundle\Model\TaskInterface');
        $task2->expects($this->any())->method('getId')->willReturn('task2');

        $task3 = $this->createMock('LoginCidadao\TaskStackBundle\Model\TaskInterface');
        $task3->expects($this->any())->method('getId')->willReturn('task3');

        $stack = new TaskStack();
        $stack->push($task1);
        $stack->push($task2);

        $this->assertTrue($stack->hasTask($task1));
        $this->assertFalse($stack->hasTask($task3));
    }
}
