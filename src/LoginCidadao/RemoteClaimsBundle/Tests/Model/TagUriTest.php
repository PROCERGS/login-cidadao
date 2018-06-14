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

use LoginCidadao\RemoteClaimsBundle\Model\TagUri;
use PHPUnit\Framework\TestCase;

class TagUriTest extends TestCase
{
    public function testValidTagUri()
    {
        $expected = 'example.com';
        $name = $this->getTagUri($expected)->getAuthorityName();

        $this->assertEquals($expected, $name);
    }

    public function testValidFullDate()
    {
        $expected = '2017-11-30';

        $tag = "tag:example.com,{$expected}:my_claim";
        $tagUri = TagUri::createFromString($tag);

        $this->assertEquals($expected, $tagUri->getDate());
        $this->assertEquals($tag, $tagUri->__toString());
    }

    public function testInvalidDay()
    {
        $date = '2017-11-31';
        $this->expectException('\InvalidArgumentException');
        $this->expectExceptionMessage("Invalid date: {$date}");

        $tag = "tag:example.com,{$date}:my_claim";
        TagUri::createFromString($tag);
    }

    public function testInvalidMonth()
    {
        $date = '2017-13';
        $this->expectException('\InvalidArgumentException');
        $this->expectExceptionMessage("Invalid date: {$date}");

        $tag = "tag:example.com,{$date}:my_claim";
        TagUri::createFromString($tag);
    }

    public function testValidEmailTagUri()
    {
        $expected = 'ful_ano+42b@example.com';
        $name = $this->getTagUri($expected)->getAuthorityName();

        $this->assertEquals($expected, $name);
    }

    public function testInvalidTagUri()
    {
        $this->expectException('\InvalidArgumentException');
        $this->getTagUri('example .com');
    }

    public function testInvalidEmailTagUri()
    {
        $this->expectException('\InvalidArgumentException');
        $this->getTagUri('test@example.com test');
    }

    /**
     * @param string $authorityName
     * @return TagUri
     */
    private function getTagUri($authorityName)
    {
        $tagUri = "tag:{$authorityName},2017:my_claim";

        return TagUri::createFromString($tagUri);
    }

    public function testWithFragment()
    {
        $tag = $this->getTagUri('example.com');
        $expected = $tag->__toString().'#it_works';

        $this->assertEquals($expected, $tag->withFragment('it_works')->__toString());
    }

    public function testGetters()
    {
        $tag = TagUri::createFromString('tag:example.com,2017:some-claim#fragment');

        $this->assertEquals('tag', $tag->getScheme());
        $this->assertEquals('2017', $tag->getDate());
        $this->assertEquals('some-claim', $tag->getSpecific());
        $this->assertEquals('fragment', $tag->getFragment());
        $this->assertEquals('example.com', $tag->getHost());

        $this->assertEquals('', $tag->getUserInfo());
        $this->assertEquals('', $tag->getPath());
        $this->assertEquals('', $tag->getQuery());
        $this->assertNull($tag->getPort());
    }

    public function testWith()
    {
        $methods = [
            'withScheme',
            'withUserInfo',
            'withHost',
            'withPort',
            'withPath',
            'withQuery',
        ];

        $successCount = 0;
        $tag = new TagUri();
        foreach ($methods as $method) {
            try {
                $tag->$method('dummy');
                $this->fail("Expected \BadMethodCallException when calling {$method}()");
            } catch (\BadMethodCallException $e) {
                $successCount++;
                continue;
            } catch (\Exception $e) {
                $this->fail("Expected \BadMethodCallException when calling {$method}() but got ".get_class($e));
            }
        }

        $this->assertEquals(count($methods), $successCount);
    }

    public function testSetAuthorityNameWithInvalidEmail()
    {
        $this->expectException('\InvalidArgumentException');
        (new TagUri())->setAuthorityName('@invalid');
    }

    public function testTagCreatedInteractively()
    {
        $tag = (new TagUri())
            ->setAuthorityName('example.com')
            ->setDate('2018-01')
            ->setSpecific('example');

        $this->assertEquals('tag:example.com,2018-01:example', $tag->__toString());
    }
}
