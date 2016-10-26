<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\NfgBundle\Service;


use PROCERGS\LoginCidadao\NfgBundle\Exception\NfgServiceUnavailableException;
use PROCERGS\LoginCidadao\NfgBundle\Security\Credentials;

class NfgSoap implements NfgSoapInterface
{
    /** @var \SoapClient */
    private $client;

    /** @var Credentials */
    private $credentials;

    public function __construct(\SoapClient $client, Credentials $credentials)
    {
        $this->client = $client;
        $this->credentials = $credentials;
    }

    /**
     * @return string
     */
    public function getAccessID()
    {
        $authentication = [
            'organizacao' => $this->credentials->getOrganization(),
            'usuario' => $this->credentials->getUsername(),
            'senha' => $this->credentials->getPassword(),
        ];

        $response = $this->client->ObterAccessID($authentication);

        if (false !== strpos($response->ObterAccessIDResult, ' ') || !isset($response->ObterAccessIDResult)) {
            throw new NfgServiceUnavailableException($response->ObterAccessIDResult);
        }

        return $response->ObterAccessIDResult;
    }
}
