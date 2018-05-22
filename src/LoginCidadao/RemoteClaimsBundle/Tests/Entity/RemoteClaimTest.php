<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\RemoteClaimsBundle\Tests\Entity;

use LoginCidadao\RemoteClaimsBundle\Entity\RemoteClaim;
use LoginCidadao\RemoteClaimsBundle\Model\ClaimProviderInterface;
use LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimInterface;
use LoginCidadao\RemoteClaimsBundle\Model\TagUri;

class RemoteClaimTest extends \PHPUnit_Framework_TestCase
{
    public function testRemoteClaim()
    {
        $id = 'id';
        $name = new TagUri();
        $displayName = 'some claim';
        $description = 'about my claim';
        $recommended = ['scope1', 'scope2'];
        $essential = ['scope2', 'scope3'];
        $uri = 'https://some.uri/example';

        /** @var ClaimProviderInterface $provider */
        $provider = $this->getMock('LoginCidadao\RemoteClaimsBundle\Model\ClaimProviderInterface');

        /** @var RemoteClaimInterface|RemoteClaim $remoteClaim */
        $remoteClaim = (new RemoteClaim())
            ->setId($id)
            ->setName($name)
            ->setDisplayName($displayName)
            ->setDescription($description)
            ->setProvider($provider)
            ->setUri($uri)
            ->setRecommendedScope($recommended)
            ->setEssentialScope($essential);

        $this->assertInstanceOf('LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimInterface', $remoteClaim);
        $this->assertEquals($id, $remoteClaim->getId());
        $this->assertEquals($name, $remoteClaim->getName());
        $this->assertEquals($displayName, $remoteClaim->getDisplayName());
        $this->assertEquals($description, $remoteClaim->getDescription());
        $this->assertEquals($provider, $remoteClaim->getProvider());
        $this->assertEquals($uri, $remoteClaim->getUri());
        $this->assertEquals($recommended, $remoteClaim->getRecommendedScope());
        $this->assertEquals($essential, $remoteClaim->getEssentialScope());
    }

    public function testGetNameAlwaysReturnsTagUri()
    {
        $remoteClaim = new RemoteClaim();

        $reflectionClass = new \ReflectionClass(get_class($remoteClaim));
        $nameProp = $reflectionClass->getProperty('name');
        $nameProp->setAccessible(true);
        $nameProp->setValue($remoteClaim, 'tag:example.com,2018:test');

        $this->assertInstanceOf('LoginCidadao\RemoteClaimsBundle\Model\TagUri', $remoteClaim->getName());
    }
}
