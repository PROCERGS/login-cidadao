<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Tests\Storage;

use Doctrine\ORM\EntityManager;
use LoginCidadao\OpenIDBundle\Storage\UserClaims;
use PHPUnit\Framework\TestCase;

class UserClaimsTest extends TestCase
{

    public function testGetUserClaims()
    {
        $this->assertNull((new UserClaims($this->getEntityManager()))->getUserClaims('user_id', 'scope1'));
    }

    /**
     * @return EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getEntityManager()
    {
        return $this->getMockBuilder('Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock();
    }
}
