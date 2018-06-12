<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\NfgBundle\Security;

use PHPUnit\Framework\TestCase;

class CredentialsTest extends TestCase
{
    public function testCredentials()
    {
        $credentials = new Credentials('org', 'user', 'pass');

        $this->assertEquals('org', $credentials->getOrganization());
        $this->assertEquals('user', $credentials->getUsername());
        $this->assertEquals('pass', $credentials->getPassword());
    }
}
