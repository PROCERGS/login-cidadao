<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Tests\Entity;

use LoginCidadao\CoreBundle\Entity\AccessSession;
use PHPUnit\Framework\TestCase;

class AccessSessionTest extends TestCase
{
    public function testAccessSession()
    {
        $iDhacess = new \DateTime();
        $ip = '::1';
        $username = 'myusername';
        $val = 123;

        $accessSession = new AccessSession();
        $accessSession->setIDhacess($iDhacess);
        $accessSession->setIp($ip);
        $accessSession->setUsername($username);
        $accessSession->setVal($val);

        $this->assertSame($iDhacess, $accessSession->getIDhacess());
        $this->assertSame($ip, $accessSession->getIp());
        $this->assertSame($username, $accessSession->getUsername());
        $this->assertSame($val, $accessSession->getVal());
        $this->assertNull($accessSession->getId());

        $accessSession->doStuffOnPrePersist();
        $this->assertNotSame($iDhacess, $accessSession->getIDhacess());
    }
}
