<?php

namespace LoginCidadao\TOSBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TermsOfServiceControllerTest extends WebTestCase
{
    public function testList()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/terms');
    }

    public function testNew()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/terms/new');
    }

}
