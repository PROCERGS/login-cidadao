<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\NfgBundle\Traits;

use Ejsmont\CircuitBreaker\CircuitBreakerInterface;
use PROCERGS\LoginCidadao\NfgBundle\Exception\NfgServiceUnavailableException;
use Psr\Log\LoggerInterface;

trait CircuitBreakerAwareTrait
{
    /** @var CircuitBreakerInterface */
    private $circuitBreaker;

    /** @var string */
    private $cbServiceName;

    /**
     * @param CircuitBreakerInterface|null $circuitBreaker
     * @param null $serviceName
     * @return void
     */
    public function setCircuitBreaker(CircuitBreakerInterface $circuitBreaker = null, $serviceName = null)
    {
        $this->circuitBreaker = $circuitBreaker;
        $this->cbServiceName = $serviceName;
    }

    /**
     * @return bool
     */
    protected function checkAvailable()
    {
        if ($this->circuitBreaker instanceof CircuitBreakerInterface
            && false === $this->circuitBreaker->isAvailable($this->cbServiceName)
        ) {
            throw new NfgServiceUnavailableException('NFG service is unavailable right now. Try again later.');
        }

        return true;
    }

    protected function reportSuccess()
    {
        if ($this->circuitBreaker && $this->cbServiceName) {
            $this->circuitBreaker->reportSuccess($this->cbServiceName);
        }

        if (isset($this->logger) && $this->logger instanceof LoggerInterface) {
            $this->logger->info('NFG service reported success');
        }
    }

    protected function reportFailure(\Exception $e = null)
    {
        if ($this->circuitBreaker && $this->cbServiceName) {
            $this->circuitBreaker->reportFailure($this->cbServiceName);
        }

        if (isset($this->logger) && $e && $this->logger instanceof LoggerInterface) {
            $this->logger->error("NFG reported failure: {$e->getMessage()}");
        }
    }
}
