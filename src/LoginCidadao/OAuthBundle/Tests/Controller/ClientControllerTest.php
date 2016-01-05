<?php

namespace LoginCidadao\OAuthBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ClientControllerTest extends WebTestCase
{
    public function testInitclient()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/initClient');
    }

}
