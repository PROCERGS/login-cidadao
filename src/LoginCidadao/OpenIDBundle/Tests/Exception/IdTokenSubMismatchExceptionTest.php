<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Tests\Exception;

use LoginCidadao\OpenIDBundle\Exception\IdTokenSubMismatchException;

class IdTokenSubMismatchExceptionTest extends \PHPUnit_Framework_TestCase
{

    public function test__construct()
    {
        $e = new IdTokenSubMismatchException('message', 400);

        $this->assertSame('message', $e->getMessage());
        $this->assertSame(400, $e->getCode());
    }
}
