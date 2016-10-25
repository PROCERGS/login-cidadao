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

    public function __construct($wsdl, array $options = [])
    {
        $this->client = new \SoapClient($wsdl, $this->getStreamContext($options));
    }

    /**
     * @param string $organization
     * @param string $username
     * @param string $password
     * @return NfgSoap $this
     */
    public function setCredentials(Credentials $credentials)
    {
        $this->credentials = $credentials;

        return $this;
    }

    /**
     * @return string
     */
    public function obterAccessID()
    {
        $authentication = [
            'organizacao' => $this->credentials->getOrganization(),
            'usuario' => $this->credentials->getUsername(),
            'senha' => $this->credentials->getPassword(),
        ];

        $response = $this->client->ObterAccessID($authentication);

        if (strpos($response->ObterAccessIDResult, ' ')) {
            throw new NfgServiceUnavailableException($response->ObterAccessIDResult);
        }

        return $response->ObterAccessIDResult;
    }

    private function getStreamContext($options)
    {
        if (array_key_exists('verify_https', $options)) {
            $verifyHttps = $options['verify_https'];
            unset($options['verify_https']);
            if (true === $verifyHttps) {
                return $options;
            }

            $options['stream_context'] = stream_context_create(
                [
                    'ssl' => [
                        // set some SSL/TLS specific options
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true,
                    ],
                ]
            );
        }

        return $options;
    }
}
