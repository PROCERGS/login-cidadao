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
use LoginCidadao\OpenIDBundle\Manager\ClientManager;
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
use Psr\Log\LogLevel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RemoteClaimFetcher implements RemoteClaimFetcherInterface
{
    use LoggerAwareTrait;

    /** @var  Client */
    private $httpClient;

    /** @var RemoteClaimRepository */
    private $claimRepo;

    /** @var ClientManager */
    private $clientManager;

    /** @var EntityManagerInterface */
    private $em;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /**
     * RemoteClaimFetcher constructor.
     * @param Client $httpClient
     * @param EntityManagerInterface $em
     * @param RemoteClaimRepository $claimRepository
     * @param ClientManager $clientManager
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        Client $httpClient,
        EntityManagerInterface $em,
        RemoteClaimRepository $claimRepository,
        ClientManager $clientManager,
        EventDispatcherInterface $dispatcher
    ) {
        $this->em = $em;
        $this->httpClient = $httpClient;
        $this->claimRepo = $claimRepository;
        $this->clientManager = $clientManager;
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
            $this->log(LogLevel::ERROR, "Error fetching remote claim: {$e->getMessage()}");
            throw new NotFoundHttpException($e->getMessage(), $e);
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
            $existingClaim
                ->setDescription($remoteClaim->getDescription())
                ->setRecommendedScope($remoteClaim->getRecommendedScope())
                ->setEssentialScope($remoteClaim->getEssentialScope())
                ->setDisplayName($remoteClaim->getDisplayName());
            $remoteClaim = $existingClaim;
            $newClaim = false;
        } else {
            $newClaim = true;
        }

        $provider = $this->findClaimProvider($remoteClaim->getProvider()->getClientId());
        if ($provider instanceof ClaimProviderInterface) {
            $remoteClaim->setProvider($provider);
        }

        if ($newClaim) {
            $this->em->persist($remoteClaim);
        }
        $this->em->flush();

        return $remoteClaim;
    }

    /**
     * @param TagUri $claimName
     * @return RemoteClaimInterface|null
     */
    private function getExistingRemoteClaim(TagUri $claimName)
    {
        /** @var RemoteClaimInterface|null $remoteClaim */
        $remoteClaim = $this->claimRepo->findOneBy(['name' => $claimName]);

        return $remoteClaim;
    }

    /**
     * @param string $clientId
     * @return ClientInterface
     * @throws ClaimProviderNotFoundException
     */
    private function findClaimProvider($clientId)
    {
        $client = $this->clientManager->getClientById($clientId);

        if (!$client instanceof ClaimProviderInterface) {
            throw new ClaimProviderNotFoundException('Relying Party "'.$clientId.'" not found.');
        }

        return $client;
    }
}
