<?php

namespace LoginCidadao\OpenIDBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ClientRegistrationControllerTest extends WebTestCase
{

    public function testRegister()
    {
        $client = static::createClient(array(), array('HTTPS' => true));

        $data = array(
            'redirect_uris' => array('http://google.com/test'),
            'post_logout_redirect_uris' => array('https://google.com/'),
        );

        $client->request(
            'POST',
            '/openid/connect/register',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode($data)
        );

        $this->assertJsonResponse($client->getResponse(), 201, false);
        $response = json_decode($client->getResponse()->getContent());
        $this->assertNotEmpty($response->client_id);
        $this->assertNotEmpty($response->client_secret);
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

    public function testRegisterInvalidRedirectUri()
    {
        $client = static::createClient(array(), array('HTTPS' => true));

        $data = array(
            'redirect_uris' => array('this.is.an.invalid.test'),
        );

        $client->request(
            'POST',
            '/openid/connect/register',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode($data)
        );

        $this->assertJsonResponse($client->getResponse(), 400, false);
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals('invalid_redirect_uri', $response->error);
    }

    public function testRegisterInvalidMetadata()
    {
        $client = static::createClient(array(), array('HTTPS' => true));

        $data = array(
            'redirect_uris' => array('https://google.com/valid/'),
            'logo_uri' => 'this.is.an.invalid.uri',
        );

        $client->request(
            'POST',
            '/openid/connect/register',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode($data)
        );

        $this->assertJsonResponse($client->getResponse(), 400, false);
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals('invalid_client_metadata', $response->error);
    }
}
