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
        $response = $this->client->ObterAccessID($this->getAuthentication());

        if (false !== strpos($response->ObterAccessIDResult, ' ') || !isset($response->ObterAccessIDResult)) {
            throw new NfgServiceUnavailableException($response->ObterAccessIDResult);
        }

        return $response->ObterAccessIDResult;
    }

    public function getUserInfo($accessToken, $voterRegistration = null)
    {
        $params = $this->getAuthentication();
        $params['accessToken'] = $accessToken;
        if ($voterRegistration) {
            $params['voterRegistration'] = $voterRegistration;
        }

        $response = $this->client->ConsultaCadastro($params);

        // TODO: check if response if valid
        throw new \RuntimeException('Not implemented yet');
    }

    private function getAuthentication()
    {
        return [
            'organizacao' => $this->credentials->getOrganization(),
            'usuario' => $this->credentials->getUsername(),
            'senha' => $this->credentials->getPassword(),
        ];
    }
}
