<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\AccountingBundle\Service;

use GuzzleHttp\ClientInterface as HttpClientInterface;
use GuzzleHttp\Exception\ClientException;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use PROCERGS\Generic\Traits\OptionalLoggerAwareTrait;
use PROCERGS\LoginCidadao\AccountingBundle\Entity\ProcergsLink;
use PROCERGS\LoginCidadao\AccountingBundle\Entity\ProcergsLinkRepository;
use Psr\Log\LoggerAwareInterface;

class SystemsRegistryService implements LoggerAwareInterface
{
    use OptionalLoggerAwareTrait;

    /** @var HttpClientInterface */
    private $client;

    /** @var string */
    private $apiUri;

    /** @var array */
    private $headers;

    /** @var array */
    private $cache = [];

    /**
     * SystemsRegistryService constructor.
     * @param HttpClientInterface $client
     * @param array $options
     */
    public function __construct(HttpClientInterface $client, array $options)
    {
        $this->client = $client;
        $this->apiUri = $options['apiUri'];
        $this->headers = [
            'organizacao' => $options['organization'],
            'matricula' => $options['registration_number'],
            'senha' => $options['password'],
        ];
    }

    public function getSystemInitials(ClientInterface $client)
    {
        $this->log('info', "Fetching PROCERGS's system initials for client_id: {$client->getPublicId()}");
        $queries = $this->getQueries($client);

        $identifiedSystems = [];
        $systems = [];
        foreach ($queries as $query) {
            foreach (array_column($this->fetchInfo($query), 'sistema') as $system) {
                if (array_key_exists($system, $systems)) {
                    $systems[$system] += 1;
                } else {
                    $systems[$system] = 1;
                }
            }
        }
        if (count($systems) <= 0) {
            return [];
        }
        asort($systems);
        $max = max($systems);
        foreach ($systems as $key => $value) {
            if ($value === $max) {
                $identifiedSystems[] = $key;
            }
        }

        return $identifiedSystems;
    }

    public function getSystemOwners(ClientInterface $client)
    {
        $queries = $this->getQueries($client);

        $identifiedOwners = [];
        $owners = [];
        foreach ($queries as $query) {
            foreach (array_column($this->fetchInfo($query), 'clienteDono') as $owner) {
                if (array_key_exists($owner, $owners)) {
                    $owners[$owner] += 1;
                } else {
                    $owners[$owner] = 1;
                }
            }
        }
        if (count($owners) <= 0) {
            return [];
        }
        asort($owners);
        $max = max($owners);
        foreach ($owners as $key => $value) {
            if ($value === $max) {
                $identifiedOwners[] = $key;
            }
        }

        return $identifiedOwners;
    }

    private function fetchInfo($query)
    {
        $this->log('info', "Searching for '{$query}'");
        $hashKey = hash('sha256', $query);
        if (false === array_key_exists($hashKey, $this->cache)) {
            $requestUrl = str_replace('{host}', $query, $this->apiUri);

            $response = null;
            try {
                $response = $this->client->get($requestUrl, ['headers' => $this->headers]);
            } catch (ClientException $e) {
                if ($e->getResponse()->getStatusCode() === 404) {
                    $response = $e->getResponse();
                } else {
                    $this->log('info',
                        "An exception occurred when trying to fetch PROCERGS's system initials for '{$query}'",
                        ['exception' => $e]
                    );
                    throw $e;
                }
            }
            $this->cache[$hashKey] = $response->json();
        } else {
            $this->log('info', "Returning cached result for '{$query}'");
        }

        return $this->cache[$hashKey];
    }

    /**
     * @param ClientInterface[] $clients OIDC Clients mapped by ID
     * @param ProcergsLinkRepository $repo
     * @return ProcergsLink[]
     */
    public function fetchLinked(array $clients, ProcergsLinkRepository $repo)
    {
        $result = [];
        $linked = $repo->findBy(['client' => $clients]);
        foreach ($linked as $link) {
            if ($link instanceof ProcergsLink && $link->getSystemType() !== null) {
                $result[$link->getClient()->getId()] = $link;
            }
        }

        return $result;
    }

    private function getQueries(ClientInterface $client)
    {
        $urls = array_filter($client->getRedirectUris());
        if ($client->getSiteUrl()) {
            $urls[] = $client->getSiteUrl();
        }

        return array_unique($urls);
    }
}
