<?php

namespace LoginCidadao\TOSBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AgreementControllerTest extends WebTestCase
{
    public function testAsk()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/ask');
    }

    public function testAgree()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/agree');
    }

}
