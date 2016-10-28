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

use Ejsmont\CircuitBreaker\CircuitBreakerInterface;
use PROCERGS\LoginCidadao\NfgBundle\Exception\NfgServiceUnavailableException;

class SoapClientFactory
{
    /** @var CircuitBreakerInterface */
    private $circuitBreaker;

    /**
     * This service's name on the Circuit Breaker
     * @var string
     */
    private $cbServiceName;

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

            return @new \SoapClient($wsdl, $options);
        } catch (\SoapFault $e) {
            if ($this->circuitBreaker instanceof CircuitBreakerInterface) {
                $this->circuitBreaker->reportFailure($this->cbServiceName);
            }
            throw new NfgServiceUnavailableException($e->getMessage(), 500, $e);
        }
    }

    /**
     * @param CircuitBreakerInterface|null $circuitBreaker
     * @param null $serviceName
     * @return SoapClientFactory
     */
    public function setCircuitBreaker(CircuitBreakerInterface $circuitBreaker = null, $serviceName = null)
    {
        $this->circuitBreaker = $circuitBreaker;
        $this->cbServiceName = $serviceName;

        return $this;
    }

    private function isAvailable()
    {
        if ($this->circuitBreaker instanceof CircuitBreakerInterface) {
            return $this->circuitBreaker->isAvailable($this->cbServiceName);
        }

        return true;
    }
}
