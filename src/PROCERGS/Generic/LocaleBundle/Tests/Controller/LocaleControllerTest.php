<?php

namespace PROCERGS\Generic\LocaleBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LocaleControllerTest extends WebTestCase
{
    public function testSet()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/set');
    }

}
