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

use Eljam\CircuitBreaker\Breaker;
use Psr\Log\LoggerInterface;

trait CircuitBreakerAwareTrait
{
    /** @var Breaker */
    private $circuitBreaker;

    /**
     * @param Breaker|null $circuitBreaker
     * @return void
     */
    public function setCircuitBreaker(Breaker $circuitBreaker = null)
    {
        $this->circuitBreaker = $circuitBreaker;
    }

    protected function reportNoCircuitBreaker()
    {
        if (isset($this->logger) && $this->logger instanceof LoggerInterface) {
            $this->logger->warning('NFG service is running without a Circuit Breaker!');
        }
    }

    protected function reportSuccess()
    {
        if (isset($this->logger) && $this->logger instanceof LoggerInterface) {
            $this->logger->info('NFG service reported success');
        }
    }

    protected function reportFailure(\Exception $e = null)
    {
        if (isset($this->logger) && $e && $this->logger instanceof LoggerInterface) {
            $this->logger->error("NFG reported failure: {$e->getMessage()}");
        }
    }

    protected function protect(\Closure $closure)
    {
        try {
            if ($this->circuitBreaker instanceof Breaker) {
                $result = $this->circuitBreaker->protect($closure);
            } else {
                $this->reportNoCircuitBreaker();
                $result = $closure();
            }
            $this->reportSuccess();

            return $result;
        } catch (\Exception $e) {
            $this->reportFailure($e);
            throw $e;
        }
    }
}
