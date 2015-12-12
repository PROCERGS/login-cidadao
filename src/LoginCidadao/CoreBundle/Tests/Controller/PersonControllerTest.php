<?php

namespace LoginCidadao\CoreBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PersonControllerTest extends WebTestCase
{
    public function testSelf()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/person');
    }

}
