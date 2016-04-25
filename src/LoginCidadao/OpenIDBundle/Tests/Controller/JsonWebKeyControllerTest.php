<?php

namespace LoginCidadao\OpenIDBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class JsonWebKeyControllerTest extends WebTestCase
{

    public function testGet()
    {
        $client = static::createClient(array(), array('HTTPS' => true));

        $client->request('GET', '/openid/connect/jwks');

        $this->assertJsonResponse($client->getResponse(), 200);
        $data = json_decode($client->getResponse()->getContent());
        $this->assertNotEmpty($data->keys);

        // Check if the private keys is leaking
        foreach ($data->keys as $key) {
            $this->assertObjectNotHasAttribute('d', $key);
        }
    }

    protected function assertJsonResponse(
        $response,
        $statusCode = 200,
        $checkValidJson = true,
        $contentType = 'application/json'
    ) {
        $this->assertEquals(
            $statusCode,
            $response->getStatusCode(),
            $response->getContent()
        );
        $this->assertTrue(
            $response->headers->contains('Content-Type', $contentType),
            $response->headers
        );
        if ($checkValidJson) {
            $decode = json_decode($response->getContent());
            $this->assertTrue(
                ($decode != null && $decode != false),
                'is response valid json: ['.$response->getContent().']'
            );
        }
    }
}
