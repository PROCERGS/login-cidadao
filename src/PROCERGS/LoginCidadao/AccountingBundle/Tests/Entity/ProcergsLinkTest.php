<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\AccountingBundle\Tests\Entity;

use LoginCidadao\OAuthBundle\Entity\Client;
use PROCERGS\LoginCidadao\AccountingBundle\Entity\ProcergsLink;

/**
 * @codeCoverageIgnore
 */
class ProcergsLinkTest extends \PHPUnit_Framework_TestCase
{
    public function testEntity()
    {
        $client = new Client();
        $updatedAt = new \DateTime();
        $createdAt = new \DateTime();

        $link = new ProcergsLink();
        $link
            ->setClient($client)
            ->setSystemType(ProcergsLink::TYPE_INTERNAL)
            ->setCreatedAtValue();

        $link
            ->setUpdatedAt($updatedAt)
            ->setCreatedAt($createdAt);

        $this->assertNull($link->getId());
        $this->assertEquals($client, $link->getClient());
        $this->assertEquals($updatedAt, $link->getUpdatedAt());
        $this->assertEquals($createdAt, $link->getCreatedAt());
    }

    public function testInvalidType()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $link = new ProcergsLink();
        $link->setSystemType('INVALID');
    }
}
