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

use LoginCidadao\CoreBundle\Exception\RedirectResponseException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;

class RedirectResponseExceptionTest extends TestCase
{

    public function testGetResponse()
    {
        $response = new RedirectResponse('https://example.com');
        $this->assertSame($response, (new RedirectResponseException($response))->getResponse());
    }
}
