<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OAuthBundle\Tests\Entity;

use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\OAuthBundle\Entity\AccessToken;
use LoginCidadao\OAuthBundle\Entity\Client;
use LoginCidadao\OpenIDBundle\Entity\ClientMetadata;

class AccessTokenTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group time-sensitive
     */
    public function testEntity()
    {
        $idToken = 'id_token';

        $accessToken = new AccessToken();
        $accessToken
            ->setIdToken($idToken)
            ->setCreatedAtValue();

        $this->assertFalse($accessToken->hasExpired());

        $accessToken->setExpired();
        sleep(2);

        $this->assertEquals($idToken, $accessToken->getIdToken());
        $this->assertTrue($accessToken->hasExpired());
        $this->assertInstanceOf('\DateTime', $accessToken->getCreatedAt());
    }

    public function testGetUserIdPairwise()
    {
        $id = '123';
        $sectorIdentifier = 'https://sector.identifier';

        /** @var ClientMetadata|\PHPUnit_Framework_MockObject_MockObject $metadata */
        $metadata = $this->getMock('LoginCidadao\OpenIDBundle\Entity\ClientMetadata');
        $metadata->expects($this->once())
            ->method('getSubjectType')->willReturn('pairwise');
        $metadata->expects($this->once())
            ->method('getSectorIdentifier')->willReturn($sectorIdentifier);

        /** @var PersonInterface|\PHPUnit_Framework_MockObject_MockObject $person */
        $person = $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');
        $person->expects($this->once())
            ->method('getId')->willReturn($id);

        $client = new Client();
        $client->setMetadata($metadata);

        $accessToken = new AccessToken();
        $accessToken->setClient($client);
        $accessToken->setUser($person);

        $salt = 'secret';
        $expected = hash('sha256', $sectorIdentifier.$id.$salt);
        $this->assertEquals($expected, $accessToken->getUserId($salt));
    }

    public function testGetUserIdPublic()
    {
        $id = '123';
        $salt = 'secret';

        /** @var ClientMetadata|\PHPUnit_Framework_MockObject_MockObject $metadata */
        $metadata = $this->getMock('LoginCidadao\OpenIDBundle\Entity\ClientMetadata');
        $metadata->expects($this->once())
            ->method('getSubjectType')->willReturn('public');

        /** @var PersonInterface|\PHPUnit_Framework_MockObject_MockObject $person */
        $person = $this->getMock('LoginCidadao\CoreBundle\Model\PersonInterface');
        $person->expects($this->any())
            ->method('getId')->willReturn($id);

        $client = new Client();

        $accessToken = new AccessToken();
        $accessToken->setUser($person);

        $this->assertEquals($id, $accessToken->getUserId($salt));

        $accessToken->setClient($client);
        $this->assertEquals($id, $accessToken->getUserId($salt));

        $client->setMetadata($metadata);
        $this->assertEquals($id, $accessToken->getUserId($salt));
    }
}
