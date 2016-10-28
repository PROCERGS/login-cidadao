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

class SoapClientFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testInvalidWsdl()
    {
        $factory = new SoapClientFactory();

        try {
            $factory->createClient('invalid', true);
        } catch (\Exception $e) {
            $this->assertInstanceOf('PROCERGS\LoginCidadao\NfgBundle\Exception\NfgServiceUnavailableException', $e);
            $this->assertInstanceOf('SoapFault', $e->getPrevious());
        }
    }

    public function testCircuitBreakerClosed()
    {
        $serviceName = 'service';
        $circuitBreaker = $this->getCircuitBreaker($serviceName, true);

        $factory = new SoapClientFactory();
        $factory->setCircuitBreaker($circuitBreaker->reveal(), $serviceName);

        try {
            $factory->createClient('invalid', true);
        } catch (\Exception $e) {
            $this->assertInstanceOf('PROCERGS\LoginCidadao\NfgBundle\Exception\NfgServiceUnavailableException', $e);
            $this->assertInstanceOf('SoapFault', $e->getPrevious());
        }
    }

    public function testCircuitBreakerOpen()
    {
        $serviceName = 'service';
        $circuitBreaker = $this->getCircuitBreaker($serviceName, false);

        $factory = new SoapClientFactory();
        $factory->setCircuitBreaker($circuitBreaker->reveal(), $serviceName);

        try {
            $factory->createClient('invalid', true);
        } catch (\Exception $e) {
            $this->assertInstanceOf('PROCERGS\LoginCidadao\NfgBundle\Exception\NfgServiceUnavailableException', $e);
            $this->assertNull($e->getPrevious());
        }
    }

    private function getCircuitBreaker($serviceName, $isAvailable)
    {
        $circuitBreaker = $this->prophesize('\Ejsmont\CircuitBreaker\CircuitBreakerInterface');
        $circuitBreaker->isAvailable($serviceName)->willReturn($isAvailable)->shouldBeCalled();
        if ($isAvailable) {
            $circuitBreaker->reportFailure($serviceName)->shouldBeCalled();
        }

        return $circuitBreaker;
    }
}
