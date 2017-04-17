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

use LoginCidadao\TaskStackBundle\Model\UrlTaskTarget;

class UrlTaskTargetTest extends \PHPUnit_Framework_TestCase
{
    public function testRouteTaskTarget()
    {
        $url = 'my_url';
        $target = new UrlTaskTarget($url);

        $this->assertEquals($url, $target->getUrl());
    }
}
