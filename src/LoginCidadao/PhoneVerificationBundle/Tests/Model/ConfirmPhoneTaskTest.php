<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\PhoneVerificationBundle\Tests\Model;

use LoginCidadao\PhoneVerificationBundle\Model\ConfirmPhoneTask;
use LoginCidadao\TaskStackBundle\Model\RouteTaskTarget;

class ConfirmPhoneTaskTest extends \PHPUnit_Framework_TestCase
{
    public function testTask()
    {
        $id = random_int(1, 9999);
        $task = new ConfirmPhoneTask($id);
        /** @var RouteTaskTarget $target */
        $target = $task->getTarget();
        $route = 'lc_verify_phone';

        $this->assertEquals("lc.confirm_phone_{$id}", $task->getId());
        $this->assertInstanceOf('LoginCidadao\TaskStackBundle\Model\RouteTaskTarget', $target);
        $this->assertEquals($route, $target->getRoute());
        $this->assertEquals($id, $target->getParameters()['id']);
        $this->assertContains($route, $task->getRoutes());
        $this->assertFalse($task->isMandatory());
    }
}
