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

use LoginCidadao\TaskStackBundle\Model\RouteTaskTarget;

class RouteTaskTargetTest extends \PHPUnit_Framework_TestCase
{
    public function testRouteTaskTarget()
    {
        $route = 'route_name';
        $params = ['key1' => 'value1'];
        $target = new RouteTaskTarget($route, $params);

        $this->assertEquals($route, $target->getRoute());
        $this->assertEquals($params, $target->getParameters());
    }
}
