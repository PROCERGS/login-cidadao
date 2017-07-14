<?php

namespace LoginCidadao\CoreBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{

    /**
     * @dataProvider urlProvider
     */
    public function testAnonymousPages($url)
    {
        $client = self::createClient(array(), array('HTTPS' => true));
        $client->request('GET', $url);

        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    /**
     * @dataProvider urlProvider
     */
    public function testHttps($url)
    {
        $client = self::createClient(array(), array('HTTPS' => false));
        $client->request('GET', $url);

        $this->assertFalse($client->getResponse()->isSuccessful());
        $this->assertEquals(301, $client->getResponse()->getStatusCode());
    }

    public function urlProvider()
    {
        return array(
            array('/login'),
            array('/about'),
            array('/privacy'),
            array('/contact'),
            array('/help'),
        );
    }
}
