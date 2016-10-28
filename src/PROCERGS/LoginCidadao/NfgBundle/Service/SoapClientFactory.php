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
use PROCERGS\LoginCidadao\NfgBundle\Traits\CircuitBreakerAwareTrait;

class SoapClientFactory
{
    use CircuitBreakerAwareTrait;

    public function createClient($wsdl, $verifyHttps = true)
    {
        try {
            if (!$this->isAvailable()) {
                throw new NfgServiceUnavailableException('Circuit Breaker open. Not trying again.');
            }
            $options = [];
            if (!$verifyHttps) {
                $options['stream_context'] = stream_context_create(
                    [
                        'ssl' => [
                            // disable SSL/TLS security checks
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true,
                        ],
                    ]
                );
            }

            $client = $this->instantiateSoapClient($wsdl, $options);
            $this->reportSuccess();

            return $client;
        } catch (\SoapFault $e) {
            $this->reportFailure();
            throw new NfgServiceUnavailableException($e->getMessage(), 500, $e);
        }
    }

    public function instantiateSoapClient($wsdl, $options)
    {
        return @new \SoapClient($wsdl, $options);
    }
}
