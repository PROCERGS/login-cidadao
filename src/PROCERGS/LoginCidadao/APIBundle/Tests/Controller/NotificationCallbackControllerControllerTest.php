<?php

namespace PROCERGS\LoginCidadao\APIBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class NotificationCallbackControllerControllerTest extends WebTestCase
{
    public function testGetfailedbyclient()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/failed');
    }

}
