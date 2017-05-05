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

use LoginCidadao\TaskStackBundle\Model\AbstractTask;

class AbstractTaskTest extends \PHPUnit_Framework_TestCase
{
    public function testAbstractTask()
    {
        /** @var AbstractTask|\PHPUnit_Framework_MockObject_MockObject $task */
        $task = $this->getMockForAbstractClass('LoginCidadao\TaskStackBundle\Model\AbstractTask');
        $task->expects($this->atLeastOnce())->method('getRoutes')->willReturn(['foo', 'bar']);

        $this->assertTrue($task->isTaskRoute('foo'));
        $this->assertTrue($task->isTaskRoute('bar'));
        $this->assertFalse($task->isTaskRoute('foobar'));
    }
}
