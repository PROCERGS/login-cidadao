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
