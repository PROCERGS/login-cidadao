<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Tests\Event;

use LoginCidadao\CoreBundle\Entity\Authorization;
use LoginCidadao\CoreBundle\Entity\Person;
use LoginCidadao\OAuthBundle\Entity\Client;
use LoginCidadao\OpenIDBundle\Event\AuthorizationEvent;
use PHPUnit\Framework\TestCase;

class AuthorizationEventTest extends TestCase
{
    public function testEvent()
    {
        $person = new Person();
        $client = new Client();
        $scope = 'scope1 scope2';
        $authorization = new Authorization();
        $authorization->setPerson($person);
        $authorization->setClient($client);
        $authorization->setScope($scope);

        $explodedScope = explode(' ', $scope);

        $event = new AuthorizationEvent($person, $client, $scope);

        $remoteClaim1 = $this->createMock('LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimInterface');
        $remoteClaim2 = $this->createMock('LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimInterface');

        $event->setRemoteClaims([$remoteClaim1]);
        $event->addRemoteClaim($remoteClaim2);

        $this->assertEquals($person, $event->getPerson());
        $this->assertEquals($client, $event->getClient());
        $this->assertEquals($explodedScope, $event->getScope());
        $this->assertNull($event->getAuthorization());
        $this->assertNotEmpty($event->getRemoteClaims());
        $this->assertContains($remoteClaim1, $event->getRemoteClaims());
        $this->assertContains($remoteClaim2, $event->getRemoteClaims());
        $this->assertCount(2, $event->getRemoteClaims());

        $event->setAuthorization($authorization);

        $this->assertEquals($authorization, $event->getAuthorization());
    }
}
