<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Tests\Task;

use LoginCidadao\OpenIDBundle\Task\CompleteUserInfoTask;

class CompleteUserInfoTaskTest extends \PHPUnit_Framework_TestCase
{
    public function testTaskWithNonce()
    {
        $clientId = '1234_abcde';
        $scope = 'scope1 scope2 scope3';
        $nonce = 'noncehere';
        $expectedId = "lc.task.complete_userinfo_{$clientId}_{$nonce}";

        $task = new CompleteUserInfoTask($clientId, $scope, $nonce);

        $this->assertEquals($clientId, $task->getClientId());
        $this->assertEquals($scope, $task->getScope());
        $this->assertContains($clientId, $task->getId());
        $this->assertContains($nonce, $task->getId());
        $this->assertEquals($expectedId, $task->getId());
    }

    public function testTaskWithoutNonce()
    {
        $clientId = '1234_abcde';
        $scope = 'scope1 scope2 scope3';
        $expectedId = "lc.task.complete_userinfo_{$clientId}";
        $routes = [
            'fos_user_registration_confirm',
            'dynamic_form',
            'dynamic_form_skip',
            'wait_valid_email',
            'dynamic_form_location',
        ];

        $task = new CompleteUserInfoTask($clientId, $scope);

        $this->assertEquals($clientId, $task->getClientId());
        $this->assertEquals($scope, $task->getScope());
        $this->assertContains($clientId, $task->getId());
        $this->assertEquals($expectedId, $task->getId());
        $this->assertFalse($task->isMandatory());
        $this->assertInstanceOf('LoginCidadao\TaskStackBundle\Model\RouteTaskTarget', $task->getTarget());
        $this->assertEquals(count($routes), count($task->getRoutes()));
        foreach ($routes as $route) {
            $this->assertContains($route, $task->getRoutes());
        }
    }

    public function testConstruct()
    {
        try {
            new CompleteUserInfoTask(null, null);
            $this->fail('\InvalidArgumentException was not thrown for client_id');
        } catch (\InvalidArgumentException $e) {
            $this->assertContains('client_id', $e->getMessage());
        }

        try {
            new CompleteUserInfoTask('', null);
            $this->fail('\InvalidArgumentException was not thrown for client_id');
        } catch (\InvalidArgumentException $e) {
            $this->assertContains('client_id', $e->getMessage());
        }

        try {
            new CompleteUserInfoTask('something', null);
            $this->fail('\InvalidArgumentException was not thrown for scope');
        } catch (\InvalidArgumentException $e) {
            $this->assertContains('scope', $e->getMessage());
        }

        try {
            new CompleteUserInfoTask('something', '');
            $this->fail('\InvalidArgumentException was not thrown for scope');
        } catch (\InvalidArgumentException $e) {
            $this->assertContains('scope', $e->getMessage());
        }
    }
}
