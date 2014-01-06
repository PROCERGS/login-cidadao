<?php

namespace PROCERGS\LoginCidadao\UIBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PersonControllerTest extends WebTestCase
{
    public function testRevokeauthorization()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/person/authorization/{id}/revoke');
    }

}
