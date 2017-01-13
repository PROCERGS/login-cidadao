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

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class SystemsRegistryService
{
    /** @var Client */
    private $client;

    /** @var string */
    private $apiUri;

    /** @var array */
    private $headers;

    /**
     * SystemsRegistryService constructor.
     * @param Client $client
     * @param array $options
     */
    public function __construct(Client $client, array $options)
    {
        $this->client = $client;
        $this->apiUri = $options['apiUri'];
        $this->headers = [
            'organizacao' => $options['organization'],
            'matricula' => $options['registration_number'],
            'senha' => $options['password'],
        ];
    }

    public function getSystemInitials($urls)
    {
        $identifiedSystems = [];
        $systems = [];
        foreach ($urls as $url) {
            foreach (array_column($this->fetchUrlInfo($url), 'sistema') as $system) {
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

    private function fetchUrlInfo($url)
    {
        $url = parse_url($url);
        $requestUrl = str_replace('{host}', $url['host'], $this->apiUri);

        $response = null;
        try {
            $response = $this->client->get($requestUrl, ['headers' => $this->headers]);
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                $response = $e->getResponse();
            }
        }

        return $response->json();
    }
}
