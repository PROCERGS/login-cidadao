<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OAuthBundle\Tests\Model;

use LoginCidadao\OAuthBundle\Entity\Client;
use LoginCidadao\OAuthBundle\Model\ClientUser;
use PHPUnit\Framework\TestCase;

class ClientUserTest extends TestCase
{

    public function testClientUser()
    {
        $client = (new Client())
            ->setId('123');
        $client->setRandomId('abc');

        $clientUser = new ClientUser($client);

        $clientUser->eraseCredentials();
        $this->assertSame($client, $clientUser->getClient());
        $this->assertNull($clientUser->getPassword());
        $this->assertNull($clientUser->getSalt());
        $this->assertSame(['ROLE_API_CLIENT'], $clientUser->getRoles());
        $this->assertSame('client:123_abc', $clientUser->getUsername());
    }
}
