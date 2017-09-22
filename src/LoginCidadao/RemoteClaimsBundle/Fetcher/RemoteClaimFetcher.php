<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\RemoteClaimsBundle\Fetcher;

use GuzzleHttp\Client;
use League\Uri\Schemes\Http;
use LoginCidadao\RemoteClaimsBundle\Entity\RemoteClaim;
use LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimFetcherInterface;
use LoginCidadao\RemoteClaimsBundle\Model\TagUri;
use LoginCidadao\RemoteClaimsBundle\Parser\RemoteClaimParser;
use LoginCidadao\OAuthBundle\Entity\Client as ClaimProvider;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RemoteClaimFetcher implements RemoteClaimFetcherInterface
{
    /** @var  Client */
    private $httpClient;

    /**
     * RemoteClaimFetcher constructor.
     * @param Client $httpClient
     */
    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function fetchRemoteClaim($claimUri)
    {
        try {
            $uri = Http::createFromString($claimUri);
        } catch (\Exception $e) {
            $claimName = TagUri::createFromString($claimUri);
            $uri = $this->discoverClaimUri($claimName);
        }

        $response = $this->httpClient->get($uri);
        $body = $response->getBody()->__toString();

        $remoteClaim = RemoteClaimParser::parseClaim($body, new RemoteClaim(), new ClaimProvider());

        return $remoteClaim;
    }

    /**
     * @param TagUri $claimName
     * @return string
     */
    public function discoverClaimUri(TagUri $claimName)
    {
        $uri = Http::createFromComponents([
            'host' => $claimName->getAuthorityName(),
            'query' => http_build_query([
                'resource' => $claimName,
                'rel' => 'http://openid.net/specs/connect/1.0/claim',
            ]),
        ]);

        $response = $this->httpClient->get($uri);
        $json = json_decode($response->getBody());

        foreach ($json->links as $link) {
            if ($link->rel === 'http://openid.net/specs/connect/1.0/claim'
                && $json->subject === $claimName->__toString()) {
                return $link->href;
            }
        }

        throw new NotFoundHttpException("Couldn't find the Claim's URI");
    }
}
