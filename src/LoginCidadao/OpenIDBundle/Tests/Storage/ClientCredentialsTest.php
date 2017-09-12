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

use Doctrine\ORM\EntityManagerInterface;
use LoginCidadao\OAuthBundle\Entity\Client;
use LoginCidadao\OAuthBundle\Entity\ClientRepository;
use LoginCidadao\OpenIDBundle\Storage\ClientCredentials;

class ClientCredentialsTest extends \PHPUnit_Framework_TestCase
{
    public function testCheckClientCredentials()
    {
        $clientId = '123_randomId';
        $clientSecret = 'client_secret';

        $client = new Client();
        $client->setId(123);
        $client->setRandomId('randomId');
        $client->setSecret($clientSecret);

        $repo = $this->getClientRepository();
        $repo->expects($this->once())->method('findOneBy')->willReturn($client);

        $em = $this->getEntityManager();
        $em->expects($this->once())->method('getRepository')
            ->with('LoginCidadaoOAuthBundle:Client')->willReturn($repo);

        $clientCredentials = new ClientCredentials($em);
        $result = $clientCredentials->checkClientCredentials($clientId, $clientSecret);

        $this->assertTrue($result);
    }

    public function testCheckInvalidClientCredentials()
    {
        $clientId = '123';
        $clientSecret = 'client_secret';

        $client = new Client();
        $client->setId(123);
        $client->setRandomId('randomId');
        $client->setSecret($clientSecret);

        $repo = $this->getClientRepository();
        $repo->expects($this->once())->method('find')->willReturn($client);

        $em = $this->getEntityManager();
        $em->expects($this->once())->method('getRepository')
            ->with('LoginCidadaoOAuthBundle:Client')->willReturn($repo);

        $clientCredentials = new ClientCredentials($em);
        $result = $clientCredentials->checkClientCredentials($clientId, 'wrong');

        $this->assertFalse($result);
    }

    public function testCheckNonExistentClient()
    {
        $repo = $this->getClientRepository();
        $repo->expects($this->once())->method('find')->willReturn(null);

        $em = $this->getEntityManager();
        $em->expects($this->once())->method('getRepository')
            ->with('LoginCidadaoOAuthBundle:Client')->willReturn($repo);

        $clientCredentials = new ClientCredentials($em);
        $result = $clientCredentials->checkClientCredentials(123, 'wrong');

        $this->assertFalse($result);
    }

    /**
     * @return EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getEntityManager()
    {
        $em = $this->getMock('Doctrine\ORM\EntityManagerInterface');

        return $em;
    }

    /**
     * @return ClientRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getClientRepository()
    {
        $repo = $this->getMockBuilder('LoginCidadao\OAuthBundle\Entity\ClientRepository')
            ->disableOriginalConstructor()->getMock();

        return $repo;
    }
}
