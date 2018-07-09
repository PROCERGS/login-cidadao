<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Tests\EventListener;

use LoginCidadao\CoreBundle\Entity\Authorization;
use LoginCidadao\CoreBundle\Entity\Person;
use LoginCidadao\OAuthBundle\Entity\Client;
use LoginCidadao\OpenIDBundle\Event\AuthorizationEvent;
use LoginCidadao\OpenIDBundle\EventListener\OAuthEventSubscriber;
use LoginCidadao\OpenIDBundle\LoginCidadaoOpenIDEvents;
use PHPUnit\Framework\TestCase;

class OAuthEventSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents()
    {
        $this->assertArrayHasKey(
            LoginCidadaoOpenIDEvents::NEW_AUTHORIZATION,
            OAuthEventSubscriber::getSubscribedEvents()
        );
        $this->assertArrayHasKey(
            LoginCidadaoOpenIDEvents::UPDATE_AUTHORIZATION,
            OAuthEventSubscriber::getSubscribedEvents()
        );
    }

    public function testOnNewAuthorization()
    {
        $person = new Person();
        $client = new Client();
        $scope = 'scope1 scope2';
        $event = new AuthorizationEvent($person, $client, $scope);

        $subscriber = new OAuthEventSubscriber();
        $subscriber->onNewAuthorization($event);

        $authorization = $event->getAuthorization();
        $this->assertNotNull($authorization);
        $this->assertInstanceOf('LoginCidadao\CoreBundle\Entity\Authorization', $authorization);

        $this->assertEquals($person, $authorization->getPerson());
        $this->assertEquals($client, $authorization->getClient());
        $this->assertContains('scope1', $authorization->getScope());
        $this->assertContains('scope2', $authorization->getScope());
    }

    public function testOnUpdateAuthorization()
    {
        $oldScope = 'scope0 scope1';
        $newScope = 'scope2 scope3';
        $person = new Person();
        $client = new Client();

        $authorization = new Authorization();
        $authorization->setScope($oldScope);

        $event = new AuthorizationEvent($person, $client, $newScope);
        $event->setAuthorization($authorization);

        $subscriber = new OAuthEventSubscriber();
        $subscriber->onUpdateAuthorization($event);

        $this->assertContains('scope0', $authorization->getScope());
        $this->assertContains('scope1', $authorization->getScope());
        $this->assertContains('scope2', $authorization->getScope());
        $this->assertContains('scope3', $authorization->getScope());
    }

    public function testOnUpdateMissingAuthorization()
    {
        $newScope = 'scope2 scope3';
        $person = new Person();
        $client = new Client();

        $event = new AuthorizationEvent($person, $client, $newScope);

        $subscriber = new OAuthEventSubscriber();
        $subscriber->onUpdateAuthorization($event);

        $this->assertNull($event->getAuthorization());
    }
}
