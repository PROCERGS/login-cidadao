<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Tests\Entity;

use LoginCidadao\CoreBundle\Entity\Authorization;
use LoginCidadao\CoreBundle\Entity\Person;
use LoginCidadao\OAuthBundle\Entity\Client;
use PHPUnit\Framework\TestCase;

class AuthorizationTest extends TestCase
{
    public function testEntity()
    {
        $date = new \DateTime();
        $person = new Person();
        $client = new Client();
        $authorization = new Authorization();
        $authorization->setClient($client);
        $authorization->setPerson($person);
        $authorization->setScope(null);
        $authorization->setCreatedAtValue();

        $this->assertNull($authorization->getId());
        $this->assertEquals($client, $authorization->getClient());
        $this->assertEquals($person, $authorization->getPerson());

        $autoDate = $authorization->getCreatedAt();
        $this->assertInstanceOf('\DateTime', $autoDate);
        $authorization->setCreatedAtValue();
        $this->assertEquals($autoDate, $authorization->getCreatedAt());

        $authorization->setCreatedAt($date);
        $this->assertEquals($date, $authorization->getCreatedAt());
    }

    public function testEmptyScope()
    {
        $authorization = new Authorization();
        $authorization->setScope(null);

        $this->assertNotEmpty($authorization->getScope());
        $this->assertTrue($authorization->hasScopes('public_profile'));
        $this->assertContains('public_profile', $authorization->getScope());
    }

    public function testScopeString()
    {
        $scope = 'scope1 scope2 scope3';
        $authorization = new Authorization();
        $authorization->setScope($scope);

        $this->assertNotEmpty($authorization->getScope());
        $this->assertContains('scope1', $authorization->getScope());
        $this->assertContains('scope2', $authorization->getScope());
        $this->assertContains('scope3', $authorization->getScope());
        $this->assertContains('public_profile', $authorization->getScope());
    }

    public function testSingleScopeString()
    {
        $scope = 'scope1';
        $authorization = new Authorization();
        $authorization->setScope($scope);

        $this->assertNotEmpty($authorization->getScope());
        $this->assertContains('scope1', $authorization->getScope());
        $this->assertContains('public_profile', $authorization->getScope());
    }

    public function testScopeArray()
    {
        $scope = ['scope1', 'scope2', 'scope3'];
        $authorization = new Authorization();
        $authorization->setScope($scope);

        $this->assertNotEmpty($authorization->getScope());
        $this->assertContains('scope1', $authorization->getScope());
        $this->assertContains('scope2', $authorization->getScope());
        $this->assertContains('scope3', $authorization->getScope());
        $this->assertContains('public_profile', $authorization->getScope());
    }

    public function testHasScopes()
    {
        $scope = 'scope1 scope2';
        $authorization = new Authorization();
        $authorization->setScope($scope);

        $this->assertTrue($authorization->hasScopes('public_profile'));
        $this->assertTrue($authorization->hasScopes($scope));
        $this->assertFalse($authorization->hasScopes('invalid_scope'));
    }

    public function testEnforceArray()
    {
        $null = Authorization::enforceArray(null);
        $false = Authorization::enforceArray(false);
        $true = Authorization::enforceArray(true);
        $empty = Authorization::enforceArray('');
        $oneScope = Authorization::enforceArray('scope1');
        $twoScopes = Authorization::enforceArray('scope1 scope2');

        $this->assertCount(0, $null);
        $this->assertCount(0, $false);
        $this->assertCount(0, $true);
        $this->assertCount(0, $empty);
        $this->assertCount(1, $oneScope);
        $this->assertCount(2, $twoScopes);
    }
}
