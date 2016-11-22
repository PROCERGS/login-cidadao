<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\NfgBundle\Test\Service;

use PROCERGS\LoginCidadao\NfgBundle\Service\SoapClientFactory;

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

    public function testOpenClosedCircuitBreaker()
    {
        $serviceName = 'service';
        $circuitBreaker = $this->getCircuitBreaker($serviceName, true);
        $circuitBreaker->expects($this->atLeastOnce())
            ->method('reportFailure')->with($serviceName);

        $factory = new SoapClientFactory();
        $factory->setCircuitBreaker($circuitBreaker, $serviceName);

        try {
            $factory->createClient('invalid', true);
        } catch (\Exception $e) {
            $this->assertInstanceOf('PROCERGS\LoginCidadao\NfgBundle\Exception\NfgServiceUnavailableException', $e);
            $this->assertInstanceOf('SoapFault', $e->getPrevious());
        }
    }

    public function testSuccess()
    {
        $serviceName = 'service';
        $circuitBreaker = $this->getCircuitBreaker($serviceName, true);
        $circuitBreaker->expects($this->atLeastOnce())
            ->method('reportSuccess')->with($serviceName);

        $factory = $this->getMockBuilder('PROCERGS\LoginCidadao\NfgBundle\Service\SoapClientFactory')
            ->setMethods(['instantiateSoapClient'])
            ->getMock();
        $factory->expects($this->atLeastOnce())
            ->method('instantiateSoapClient')
            ->willReturn($this->getMockBuilder('\SoapClient')->disableOriginalConstructor()->getMock());
        $factory->setCircuitBreaker($circuitBreaker, $serviceName);

        $client = $factory->createClient('invalid', true);
        $this->assertInstanceOf('\SoapClient', $client);
    }

    public function testCircuitBreakerOpen()
    {
        $serviceName = 'service';
        $circuitBreaker = $this->getCircuitBreaker($serviceName, false);

        $factory = new SoapClientFactory();
        $factory->setCircuitBreaker($circuitBreaker, $serviceName);

        try {
            $factory->createClient('invalid', true);
        } catch (\Exception $e) {
            $this->assertInstanceOf('PROCERGS\LoginCidadao\NfgBundle\Exception\NfgServiceUnavailableException', $e);
            $this->assertNull($e->getPrevious());
        }
    }

    /**
     * @param string $serviceName
     * @param boolean $isAvailable
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getCircuitBreaker($serviceName, $isAvailable)
    {
        $circuitBreaker = $this->getMock('\Ejsmont\CircuitBreaker\CircuitBreakerInterface');
        $circuitBreaker->expects($this->atLeastOnce())
            ->method('isAvailable')->with($serviceName)->willReturn($isAvailable);

        return $circuitBreaker;
    }
}
