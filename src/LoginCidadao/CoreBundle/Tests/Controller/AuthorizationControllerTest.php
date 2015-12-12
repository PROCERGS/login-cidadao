<?php

namespace LoginCidadao\CoreBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthorizationControllerTest extends WebTestCase
{
    public function testUserauthorizations()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/authorizations');
    }

}
