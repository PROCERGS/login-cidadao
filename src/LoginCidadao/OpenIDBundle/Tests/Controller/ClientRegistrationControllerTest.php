<?php

namespace LoginCidadao\OpenIDBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ClientRegistrationControllerTest extends WebTestCase
{

    public function testRegister()
    {
        $client = static::createClient();

        $data = array(
            'redirect_uris' => array('http://this.is.a.test/callback')
        );

        $client->request(
            'POST', '/openid/connect/register', array(), array(),
            array('CONTENT_TYPE' => 'application/json'), json_encode($data)
        );

        $this->assertJsonResponse($client->getResponse(), 201, false);
    }

    protected function assertJsonResponse($response, $statusCode = 200,
                                          $checkValidJson = true,
                                          $contentType = 'application/json')
    {
        $this->assertEquals(
            $statusCode, $response->getStatusCode(), $response->getContent()
        );
        $this->assertTrue(
            $response->headers->contains('Content-Type', $contentType),
            $response->headers
        );
        if ($checkValidJson) {
            $decode = json_decode($response->getContent());
            $this->assertTrue(($decode != null && $decode != false),
                'is response valid json: ['.$response->getContent().']'
            );
        }
    }
}
