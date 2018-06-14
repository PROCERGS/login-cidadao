<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Tests\Manager;

use Doctrine\ORM\EntityManager;
use LoginCidadao\OpenIDBundle\Manager\ScopeManager;
use OAuth2\ServerBundle\Entity\Scope;
use PHPUnit\Framework\TestCase;

class ScopeManagerTest extends TestCase
{
    const SCOPE_CLASS = 'OAuth2\ServerBundle\Entity\Scope';

    public function testFindScopeByScope()
    {
        $manager = $this->getScopeManager();
        $manager->setScopes('scope1 scope2 scope3');

        foreach (['scope1', 'scope2', 'scope3', 'tag:test'] as $scopeName) {
            /** @var Scope $scope */
            $scope = $manager->findScopeByScope($scopeName);
            $this->assertInstanceOf(self::SCOPE_CLASS, $scope);
            $this->assertSame($scopeName, $scope->getScope());
        }
    }

    public function testFindNonExistentScope()
    {
        $manager = $this->getScopeManager();
        $manager->setScopes('scope1 scope2 scope3');

        $scope = $manager->findScopeByScope('foobar');

        $this->assertNotInstanceOf(self::SCOPE_CLASS, $scope);
        $this->assertNull($scope);
    }

    public function testSetScopes()
    {
        $scopes = [
            '',
            [],
            'scope1 scope2 scope3',
            ['scope1'],
            ['scope1', 'scope2', 'scope3'],
        ];
        $manager = $this->getScopeManager();

        foreach ($scopes as $scope) {
            $manager->setScopes($scope);
        }

        $this->assertInstanceOf(self::SCOPE_CLASS, $manager->findScopeByScope('scope1'));
    }

    public function testFindScopesByScopes()
    {
        $manager = $this->getScopeManager();
        $manager->setScopes('scope1 scope2 scope3');

        $scopes = $manager->findScopesByScopes(['scope0', 'scope1', 'scope3']);

        $this->assertContains('scope1', array_keys($scopes));
        $this->assertContains('scope3', array_keys($scopes));
        $this->assertNotContains('scope0', array_keys($scopes));
        $this->assertNotContains('scope2', array_keys($scopes));
    }

    private function getScopeManager()
    {
        return new ScopeManager($this->getEntityManager());
    }

    /**
     * @return EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getEntityManager()
    {
        return $this->getMockBuilder('Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock();
    }
}
