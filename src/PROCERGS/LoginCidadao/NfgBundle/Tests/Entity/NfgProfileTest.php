<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\NfgBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use PROCERGS\LoginCidadao\NfgBundle\Entity\NfgProfile;

class NfgProfileTest extends TestCase
{
    public function testUpdatedAt()
    {
        $nfgProfile = new NfgProfile();
        $nfgProfile->setUpdatedAt();

        $this->assertEquals(date('Y-m-d H:i:s'), $nfgProfile->getUpdatedAt()->format('Y-m-d H:i:s'));

        $date = $nfgProfile->getUpdatedAt();
        $nfgProfile->setUpdatedAt($date);
        $this->assertEquals($date, $nfgProfile->getUpdatedAt());
    }

    public function testId()
    {
        $id = rand(42, 9999);
        $nfgProfile = new NfgProfile();
        $nfgProfile->setId($id);

        $this->assertEquals($id, $nfgProfile->getId());
    }

    public function testCpfZeroPadding()
    {
        $nfgProfile = new NfgProfile();

        $nfgProfile->setCpf('1');
        $this->assertEquals('00000000001', $nfgProfile->getCpf());

        $nfgProfile->setCpf(1);
        $this->assertEquals('00000000001', $nfgProfile->getCpf());
    }

    public function testNonNumericCpf()
    {
        $this->expectException(\InvalidArgumentException::class);
        (new NfgProfile())->setCpf('a');
    }
}
