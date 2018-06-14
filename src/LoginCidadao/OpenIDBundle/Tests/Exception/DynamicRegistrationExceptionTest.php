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

use LoginCidadao\OpenIDBundle\Exception\DynamicRegistrationException;
use PHPUnit\Framework\TestCase;

class DynamicRegistrationExceptionTest extends TestCase
{

    public function testGetData()
    {
        $code = '500';
        $message = 'My message';

        $data = (new DynamicRegistrationException($message, $code))->getData();
        $this->assertSame($code, $data['error']);
        $this->assertSame($message, $data['error_description']);
    }
}
