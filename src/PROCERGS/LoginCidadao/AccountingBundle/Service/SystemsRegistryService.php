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
use PROCERGS\LoginCidadao\AccountingBundle\Entity\ProcergsLink;
use PROCERGS\LoginCidadao\AccountingBundle\Entity\ProcergsLinkRepository;

class SystemsRegistryService
{
    /** @var Client */
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
        $hosts = $this->getHosts($client);

        $identifiedSystems = [];
        $systems = [];
        foreach ($hosts as $host) {
            foreach (array_column($this->fetchInfo($host), 'sistema') as $system) {
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
        $hosts = $this->getHosts($client);

        $identifiedOwners = [];
        $owners = [];
        foreach ($hosts as $host) {
            foreach (array_column($this->fetchInfo($host), 'clienteDono') as $owner) {
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
                    throw $e;
                }
            }
            $this->cache[$hashKey] = $response->json();
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

    private function getHosts(ClientInterface $client)
    {
        $urls = array_filter($client->getRedirectUris());
        if ($client->getSiteUrl()) {
            $urls[] = $client->getSiteUrl();
        }
        $hosts = array_unique(
            array_map(
                function ($url) {
                    return parse_url($url)['host'];
                },
                $urls
            )
        );
        if ($client->getSiteUrl()) {
            $hosts[] = $client->getSiteUrl();
        }

        return $hosts;
    }
}
