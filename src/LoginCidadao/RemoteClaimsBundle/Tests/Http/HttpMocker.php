<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\RemoteClaimsBundle\Tests\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LoginCidadao\RemoteClaimsBundle\Tests\Parser\RemoteClaimParserTest;

class HttpMocker
{
    /** @var array */
    private $requests;

    /** @var array */
    private $responses;

    /** @var mixed */
    private $data;

    /** @var Client */
    private $client;

    /**
     * HttpMocker constructor.
     * @param mixed $data Remote Claim metadata
     * @param string|null $expectedUri if a Discovery call is expected, this parameter MUST be the Claim's URI
     * @param array|null $responses
     */
    public function __construct($data = null, $expectedUri = null, array $responses = null)
    {
        if (null === $responses) {
            $this->setDefaultResponse(json_encode($data));
        } else {
            $this->responses = $responses;
        }

        if ($expectedUri !== null) {
            $discoveryResponse = json_encode([
                'subject' => $data['claim_name'],
                'links' => [
                    ['rel' => 'http://openid.net/specs/connect/1.0/claim', 'href' => $expectedUri],
                ],
            ]);
            array_unshift($this->responses,
                new Response(200, ['Content-Length' => strlen($discoveryResponse)], $discoveryResponse));
        }

        $this->requests = [];
        $stack = HandlerStack::create(new MockHandler($this->responses));
        $stack->push(Middleware::history($this->requests));

        $this->data = $data !== null ? $data : RemoteClaimParserTest::$claimMetadata;

        $this->client = new Client(['handler' => $stack]);
    }

    private function setDefaultResponse(string $metadata)
    {
        $this->responses = [
            new Response(200, ['Content-Length' => strlen($metadata)], $metadata),
        ];
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return array
     */
    public function getTransactions(): array
    {
        return $this->requests;
    }

    /**
     * @return Request[]
     */
    public function getRequests(): array
    {
        return array_column($this->getTransactions(), 'request');
    }

    /**
     * @return Response[]
     */
    public function getResponses(): array
    {
        return array_column($this->getTransactions(), 'response');
    }
}
