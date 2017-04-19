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

use LoginCidadao\TaskStackBundle\Model\IntentTask;

class IntentTaskTest extends \PHPUnit_Framework_TestCase
{
    public function testIntentTask()
    {
        $target = $this->getMockBuilder('LoginCidadao\TaskStackBundle\Model\UrlTaskTarget')
            ->disableOriginalConstructor()
            ->getMock();
        $target->expects($this->once())->method('getUrl')->willReturn('some_url');

        $task = new IntentTask($target);

        $this->assertEquals($target, $task->getTarget());
        $this->assertTrue($task->isMandatory());
        $this->assertEquals('some_url', $task->getId());
        $this->assertEmpty($task->getRoutes());
    }
}
