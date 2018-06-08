<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Tests\Exception;

use LoginCidadao\CoreBundle\Exception\RecaptchaException;
use PHPUnit\Framework\TestCase;

class RecaptchaExceptionTest extends TestCase
{

    public function testGetClass()
    {
        $this->assertSame('LoginCidadao\CoreBundle\Exception\RecaptchaException',
            (new RecaptchaException())->getClass());
    }
}
