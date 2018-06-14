<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\TaskStackBundle\Tests\Exception;

use LoginCidadao\TaskStackBundle\Exception\UnsupportedTargetException;
use PHPUnit\Framework\TestCase;

class UnsupportedTargetExceptionTest extends TestCase
{
    public function testException()
    {
        $target = $this->createMock('LoginCidadao\TaskStackBundle\Model\TaskTargetInterface');

        $e = new UnsupportedTargetException($target);

        $this->assertContains(get_class($target), $e->getMessage());
    }
}
