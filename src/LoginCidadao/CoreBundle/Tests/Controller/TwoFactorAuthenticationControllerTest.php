<?php

namespace LoginCidadao\CoreBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TwoFactorAuthenticationControllerTest extends WebTestCase
{
    public function testEnable()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/enable');
    }

    public function testDisable()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/disable');
    }

}
