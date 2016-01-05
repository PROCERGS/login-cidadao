<?php

namespace LoginCidadao\CoreBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DocumentControllerTest extends WebTestCase
{
    public function testGetgeneral()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/person/documents');
    }

}
