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

use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use LoginCidadao\LogBundle\Traits\LoggerAwareTrait;
use LoginCidadao\OAuthBundle\Entity\ClientRepository;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use LoginCidadao\RemoteClaimsBundle\Entity\RemoteClaim;
use LoginCidadao\RemoteClaimsBundle\Entity\RemoteClaimRepository;
use LoginCidadao\RemoteClaimsBundle\Event\UpdateRemoteClaimUriEvent;
use LoginCidadao\RemoteClaimsBundle\Exception\ClaimProviderNotFoundException;
use LoginCidadao\RemoteClaimsBundle\Exception\ClaimUriUnavailableException;
use LoginCidadao\RemoteClaimsBundle\Model\ClaimProviderInterface;
use LoginCidadao\RemoteClaimsBundle\Model\HttpUri;
use LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimFetcherInterface;
use LoginCidadao\RemoteClaimsBundle\Model\RemoteClaimInterface;
use LoginCidadao\RemoteClaimsBundle\Model\TagUri;
use LoginCidadao\RemoteClaimsBundle\Parser\RemoteClaimParser;
use LoginCidadao\OAuthBundle\Entity\Client as ClaimProvider;
use LoginCidadao\RemoteClaimsBundle\RemoteClaimEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RemoteClaimFetcher implements RemoteClaimFetcherInterface
{
    use LoggerAwareTrait;

    /** @var  Client */
    private $httpClient;

    /** @var RemoteClaimRepository */
    private $claimRepo;

    /** @var ClientRepository */
    private $clientRepo;

    /** @var EntityManagerInterface */
    private $em;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /**
     * RemoteClaimFetcher constructor.
     * @param Client $httpClient
     * @param EntityManagerInterface $em
     * @param RemoteClaimRepository $claimRepository
     * @param ClientRepository $clientRepository
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        Client $httpClient,
        EntityManagerInterface $em,
        RemoteClaimRepository $claimRepository,
        ClientRepository $clientRepository,
        EventDispatcherInterface $dispatcher
    ) {
        $this->em = $em;
        $this->httpClient = $httpClient;
        $this->claimRepo = $claimRepository;
        $this->clientRepo = $clientRepository;
        $this->dispatcher = $dispatcher;
    }

    public function fetchRemoteClaim($claimUri)
    {
        try {
            $uri = HttpUri::createFromString($claimUri);
        } catch (\Exception $e) {
            $claimName = TagUri::createFromString($claimUri);
            try {
                $uri = $this->discoverClaimUri($claimName);
            } catch (ClaimUriUnavailableException $e) {
                throw new NotFoundHttpException();
            }
        }

        try {
            $response = $this->httpClient->get($uri);
            $body = $response->getBody()->__toString();

            $remoteClaim = RemoteClaimParser::parseClaim($body, new RemoteClaim(), new ClaimProvider());

            return $remoteClaim;
        } catch (\Exception $e) {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @param TagUri|string $claimName
     * @return string
     */
    public function discoverClaimUri($claimName)
    {
        if (!$claimName instanceof TagUri) {
            $claimName = TagUri::createFromString($claimName);
        }

        $uri = $this->performDiscovery($claimName);

        if ($uri === false) {
            $uri = $this->discoveryFallback($claimName);
        } else {
            $event = new UpdateRemoteClaimUriEvent($claimName, $uri);
            $this->dispatcher->dispatch(RemoteClaimEvents::REMOTE_CLAIM_UPDATE_URI, $event);
        }

        return $uri;
    }

    /**
     * @param TagUri|string $claimName
     * @return mixed
     */
    private function performDiscovery(TagUri $claimName)
    {
        $uri = HttpUri::createFromComponents([
            'scheme' => 'https',
            'host' => $claimName->getAuthorityName(),
            'path' => '/.well-known/webfinger',
            'query' => http_build_query([
                'resource' => $claimName->__toString(),
                'rel' => 'http://openid.net/specs/connect/1.0/claim',
            ]),
        ]);

        try {
            $response = $this->httpClient->get($uri->__toString());
            $json = json_decode($response->getBody());

            if (property_exists($json, 'links')) {
                foreach ($json->links as $link) {
                    if ($link->rel === 'http://openid.net/specs/connect/1.0/claim'
                        && $json->subject === $claimName->__toString()) {
                        return $link->href;
                    }
                }
            }
        } catch (TransferException $e) {
            return false;
        }

        return false;
    }

    /**
     * @param $claimName
     * @return string
     */
    private function discoveryFallback(TagUri $claimName)
    {
        $remoteClaim = $this->getExistingRemoteClaim($claimName);

        if (!$remoteClaim instanceof RemoteClaimInterface || $remoteClaim->getUri() === null) {
            throw new ClaimUriUnavailableException();
        }

        return $remoteClaim->getUri();
    }

    /**
     * Fetches a RemoteClaimInterface via <code>fetchRemoteClaim</code>, persisting and returning the result.
     * @param TagUri|string $claimUri
     * @return RemoteClaimInterface
     * @throws ClaimProviderNotFoundException
     */
    public function getRemoteClaim($claimUri)
    {
        $remoteClaim = $this->fetchRemoteClaim($claimUri);

        $existingClaim = $this->getExistingRemoteClaim($remoteClaim->getName());
        if ($existingClaim instanceof RemoteClaimInterface) {
            $remoteClaim = $existingClaim;
            $newClaim = false;
        } else {
            $newClaim = true;
        }

        $provider = $this->getExistingClaimProvider($remoteClaim->getProvider());
        if ($provider instanceof ClaimProviderInterface) {
            $remoteClaim->setProvider($provider);
        }

        if ($newClaim) {
            $this->em->persist($remoteClaim);
            $this->em->flush();
        }

        return $remoteClaim;
    }

    /**
     * @param TagUri $claimName
     * @return null|RemoteClaimInterface
     */
    private function getExistingRemoteClaim(TagUri $claimName)
    {
        /** @var RemoteClaimInterface $remoteClaim */
        $remoteClaim = $this->claimRepo->findOneBy(['name' => $claimName]);

        return $remoteClaim;
    }

    /**
     * @param string[] $redirectUris
     * @return ClientInterface
     */
    private function findClaimProvider($redirectUris)
    {
        $clients = $this->clientRepo->findByRedirectUris($redirectUris);

        if (count($clients) > 1) {
            throw new \InvalidArgumentException('Ambiguous redirect_uris. More than one Relying Party found.');
        }

        return empty($clients) ? null : reset($clients);
    }

    /**
     * Gets the persisted/ORM-attached ClaimProvider
     * @param ClaimProviderInterface|null $provider
     * @return ClientInterface|null
     * @throws ClaimProviderNotFoundException
     */
    private function getExistingClaimProvider(ClaimProviderInterface $provider = null)
    {
        $existingProvider = null;
        if ($provider instanceof ClaimProviderInterface) {
            $existingProvider = $this->findClaimProvider($provider->getRedirectUris());
        }

        if ($existingProvider instanceof ClaimProviderInterface) {
            return $existingProvider;
        }

        // No pre-existing provider was found. Throw Exception!
        throw new ClaimProviderNotFoundException(
            "A Claim Provider was not found. This Identity Provider does NOT support Dynamic Claim Provider registration, so make sure it is already registered."
        );
    }
}