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

class TagUriTest extends \PHPUnit_Framework_TestCase
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
        $this->setExpectedException('\InvalidArgumentException', "Invalid date: {$date}");

        $tag = "tag:example.com,{$date}:my_claim";
        TagUri::createFromString($tag);
    }

    public function testInvalidMonth()
    {
        $date = '2017-13';
        $this->setExpectedException('\InvalidArgumentException', "Invalid date: {$date}");

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
        $this->setExpectedException('\InvalidArgumentException');
        $this->getTagUri('example .com');
    }

    public function testInvalidEmailTagUri()
    {
        $this->setExpectedException('\InvalidArgumentException');
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
}
