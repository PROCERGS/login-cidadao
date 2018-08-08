<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\AccountRecoveryBundle\Tests\Entity;

use libphonenumber\PhoneNumber;
use LoginCidadao\AccountRecoveryBundle\Entity\AccountRecoveryData;
use LoginCidadao\CoreBundle\Entity\Person;
use PHPUnit\Framework\TestCase;

class AccountRecoveryDataTest extends TestCase
{
    public function testEntity()
    {
        $data = (new AccountRecoveryData())
            ->setPerson($person = new Person())
            ->setMobile($mobile = new PhoneNumber())
            ->setEmail($email = 'user@example.com')
            ->setCreatedAt()
            ->setUpdatedAt();

        $this->assertNull($data->getId());
        $this->assertSame($person, $data->getPerson());
        $this->assertSame($mobile, $data->getMobile());
        $this->assertSame($email, $data->getEmail());
        $this->assertInstanceOf(\DateTime::class, $data->getCreatedAt());
        $this->assertInstanceOf(\DateTime::class, $data->getUpdatedAt());
    }
}
