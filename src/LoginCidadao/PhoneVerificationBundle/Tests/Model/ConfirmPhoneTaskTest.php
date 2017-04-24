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
        $task = new ConfirmPhoneTask();
        /** @var RouteTaskTarget $target */
        $target = $task->getTarget();
        $route = 'lc_verify_phone';

        $this->assertEquals('lc.confirm_phone', $task->getId());
        $this->assertInstanceOf('LoginCidadao\TaskStackBundle\Model\RouteTaskTarget', $target);
        $this->assertEquals($route, $target->getRoute());
        $this->assertContains($route, $task->getRoutes());
        $this->assertFalse($task->isMandatory());
    }
}
