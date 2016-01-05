<?php

namespace LoginCidadao\CoreBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class NotificationControllerTest extends WebTestCase
{
    public function testList()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/list');
    }

    public function testRead()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/read');
    }

}
