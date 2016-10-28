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
    protected function isAvailable()
    {
        if ($this->circuitBreaker instanceof CircuitBreakerInterface) {
            return $this->circuitBreaker->isAvailable($this->cbServiceName);
        }

        return true;
    }

    protected function reportSuccess()
    {
        if ($this->circuitBreaker && $this->cbServiceName) {
            $this->circuitBreaker->reportSuccess($this->cbServiceName);
        }
    }

    protected function reportFailure()
    {
        if ($this->circuitBreaker && $this->cbServiceName) {
            $this->circuitBreaker->reportFailure($this->cbServiceName);
        }
    }
}
