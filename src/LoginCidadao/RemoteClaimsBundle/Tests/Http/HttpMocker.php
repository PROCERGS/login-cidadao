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
use GuzzleHttp\Subscriber\History;
use GuzzleHttp\Subscriber\Mock;
use LoginCidadao\RemoteClaimsBundle\Tests\Parser\RemoteClaimParserTest;

class HttpMocker
{
    /** @var History */
    private $history;

    /** @var mixed */
    private $data;

    /** @var Client */
    private $client;

    /**
     * HttpMocker constructor.
     * @param mixed $data Remote Claim metadata
     * @param string|null $expectedUri if a Discovery call is expected, this parameter MUST be the Claim's URI
     */
    public function __construct($data = null, $expectedUri = null)
    {
        $this->history = new History();
        $this->data = $data !== null ? $data : RemoteClaimParserTest::$claimMetadata;

        $this->client = new Client();

        $metadata = json_encode($this->data);
        $length = strlen($metadata);

        $responses = ["HTTP/1.1 200 OK\r\n\Content-Length: {$length}\r\n\r\n{$metadata}"];

        if ($expectedUri !== null) {
            $discoveryResponse = json_encode([
                'subject' => $data['claim_name'],
                'links' => [
                    ['rel' => 'http://openid.net/specs/connect/1.0/claim', 'href' => $expectedUri],
                ],
            ]);
            $discoveryLen = strlen($discoveryResponse);
            array_unshift($responses,
                "HTTP/1.1 200 OK\r\n\Content-Length: {$discoveryLen}\r\n\r\n{$discoveryResponse}");
        }

        $mock = new Mock($responses);
        $this->client->getEmitter()->attach($this->history);
        $this->client->getEmitter()->attach($mock);
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return History
     */
    public function getHistory()
    {
        return $this->history;
    }
}
