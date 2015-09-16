<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{

    /**
     * @dataProvider urlProvider
     */
    public function testAnonymousPages($url)
    {
        $client = self::createClient();
        $client->request('GET', $url);

        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function urlProvider()
    {
        return array(
            array('/'),
            array('/login'),
            array('/about'),
            array('/privacy'),
            array('/contact'),
            array('/help'),
        );
    }
}
