<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\CpfVerificationBundle\Tests\Exception;

use PHPUnit\Framework\TestCase;
use PROCERGS\LoginCidadao\CpfVerificationBundle\Exception\CpfNotSubscribedToNfgException;

class CpfNotSubscribedToNfgExceptionTest extends TestCase
{
    public function testException()
    {
        $e = new CpfNotSubscribedToNfgException($cpf = '12345678901');
        $this->assertSame($cpf, $e->getCpf());
    }
}
