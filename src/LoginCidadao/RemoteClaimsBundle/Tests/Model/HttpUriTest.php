<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\RemoteClaimsBundle\Tests\Model;

use LoginCidadao\RemoteClaimsBundle\Model\HttpUri;
use PHPUnit\Framework\TestCase;

class HttpUriTest extends TestCase
{
    public function testValidCompleteUri()
    {
        $uri = 'https://user:password@example.com:8042/over/there?name=ferret#nose';
        $http = HttpUri::createFromString($uri);

        $this->assertInstanceOf('LoginCidadao\RemoteClaimsBundle\Model\HttpUri', $http);
        $this->assertEquals($uri, $http->__toString());
        $this->assertEquals('https', $http->getScheme());
        $this->assertEquals('user:password', $http->getUserInfo());
        $this->assertEquals('example.com', $http->getHost());
        $this->assertEquals(8042, $http->getPort());
        $this->assertEquals('user:password@example.com:8042', $http->getAuthority());
        $this->assertEquals('/over/there', $http->getPath());
        $this->assertEquals('name=ferret', $http->getQuery());
        $this->assertEquals('nose', $http->getFragment());
    }

    public function testValidSimpleUri()
    {
        $uri = 'http://example.com';
        $http = HttpUri::createFromString($uri);

        $this->assertInstanceOf('LoginCidadao\RemoteClaimsBundle\Model\HttpUri', $http);
        $this->assertEquals($uri, $http->__toString());
        $this->assertEquals('http', $http->getScheme());
        $this->assertEquals('', $http->getUserInfo());
        $this->assertEquals('example.com', $http->getHost());
        $this->assertNull($http->getPort());
        $this->assertEquals('example.com', $http->getAuthority());
        $this->assertEquals('', $http->getPath());
        $this->assertEquals('', $http->getQuery());
        $this->assertEquals('', $http->getFragment());
    }

    public function testWithMethods()
    {
        $http = (new HttpUri())
            ->withScheme('https')
            ->withUserInfo('user', 'password')
            ->withHost('example.com')
            ->withPort(8042)
            ->withPath('/over/there')
            ->withQuery('name=ferret')
            ->withFragment('nose');

        $uri = 'https://user:password@example.com:8042/over/there?name=ferret#nose';
        $this->assertEquals($uri, $http->__toString());
    }

    public function testInvalidUri()
    {
        $this->expectException('\InvalidArgumentException');
        $uri = 'not valid';
        HttpUri::createFromString($uri);
    }

    public function testInvalidScheme()
    {
        $this->expectException('\InvalidArgumentException');
        $uri = 'ftp://example.com';
        HttpUri::createFromString($uri);
    }

    public function testInvalidHost()
    {
        $this->expectException('\InvalidArgumentException');
        $uri = 'https://not valid.com';
        HttpUri::createFromString($uri);
    }

    public function testInvalidPath()
    {
        $this->expectException('\InvalidArgumentException');
        $uri = 'https://example.com/not valid';
        HttpUri::createFromString($uri);
    }
}
