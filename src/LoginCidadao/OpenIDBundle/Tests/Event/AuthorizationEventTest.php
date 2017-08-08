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

class AuthorizationEventTest extends \PHPUnit_Framework_TestCase
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
        $this->assertEquals($person, $event->getPerson());
        $this->assertEquals($client, $event->getClient());
        $this->assertEquals($explodedScope, $event->getScope());
        $this->assertNull($event->getAuthorization());

        $event->setAuthorization($authorization);

        $this->assertEquals($authorization, $event->getAuthorization());
    }
}