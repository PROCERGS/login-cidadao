<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\NfgBundle\Tests\Helper;

use PROCERGS\LoginCidadao\NfgBundle\Helper\UrlHelper;

class UrlHelperTest extends \PHPUnit_Framework_TestCase
{
    public function testAddToEmptyQuery()
    {
        $url = parse_url('https://dum.my/something');
        $query = isset($url['query']) ? $url['query'] : null;

        $data = ['key1' => 'val1', 'key2' => 'val2'];
        $expected = http_build_query($data);
        $result = UrlHelper::addToQuery($data, $query);

        $this->assertEquals($expected, $result);
    }

    public function testAddToQuery()
    {
        $url = parse_url('https://dum.my/something?foo=bar');
        $query = $url['query'];

        $result = UrlHelper::addToQuery(['key1' => 'val1'], $query);

        $this->assertContains('bar', $result);
        $this->assertContains('val1', $result);
    }
}
