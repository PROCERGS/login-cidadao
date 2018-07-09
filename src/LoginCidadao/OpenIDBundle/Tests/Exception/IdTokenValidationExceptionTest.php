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

use LoginCidadao\OpenIDBundle\Exception\IdTokenValidationException;
use PHPUnit\Framework\TestCase;

class IdTokenValidationExceptionTest extends TestCase
{

    public function testConstruct()
    {
        $e = new IdTokenValidationException('message', 400);

        $this->assertSame('message', $e->getMessage());
        $this->assertSame(400, $e->getCode());
    }
}
