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
}
