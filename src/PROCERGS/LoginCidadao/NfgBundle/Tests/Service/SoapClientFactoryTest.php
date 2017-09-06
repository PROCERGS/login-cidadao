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

use PROCERGS\LoginCidadao\NfgBundle\Exception\NfgServiceUnavailableException;
use PROCERGS\LoginCidadao\NfgBundle\Service\SoapClientFactory;
use Psr\Log\LoggerInterface;

class SoapClientFactoryTest extends \PHPUnit_Framework_TestCase
{
    private function getBreaker($exception = null)
    {
        $breaker = $this->getMockBuilder('Eljam\CircuitBreaker\Breaker')
            ->setConstructorArgs(['some_name'])
            ->setMethods(['protect'])
            ->getMock();

        if ($exception instanceof \Exception) {
            $breaker->expects($this->once())->method('protect')
                ->willThrowException($exception);
        } else {
            $breaker->expects($this->once())->method('protect')
                ->willReturnCallback(function (\Closure $closure) {
                    return $closure();
                });
        }

        return $breaker;
    }

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
        $factory = new SoapClientFactory();
        $factory->setCircuitBreaker($this->getBreaker());

        try {
            $factory->createClient('invalid', true);
        } catch (\Exception $e) {
            $this->assertInstanceOf('PROCERGS\LoginCidadao\NfgBundle\Exception\NfgServiceUnavailableException', $e);
            $this->assertInstanceOf('SoapFault', $e->getPrevious());
        }
    }

    public function testSuccess()
    {
        /** @var SoapClientFactory|\PHPUnit_Framework_MockObject_MockObject $factory */
        $factory = $this->getMockBuilder('PROCERGS\LoginCidadao\NfgBundle\Service\SoapClientFactory')
            ->setMethods(['instantiateSoapClient'])
            ->getMock();
        $factory->expects($this->atLeastOnce())
            ->method('instantiateSoapClient')
            ->willReturn($this->getMockBuilder('\SoapClient')->disableOriginalConstructor()->getMock());
        $factory->setCircuitBreaker($this->getBreaker());

        $client = $factory->createClient('invalid', true);
        $this->assertInstanceOf('\SoapClient', $client);
    }

    public function testCircuitBreakerOpen()
    {
        $factory = new SoapClientFactory();
        $factory->setCircuitBreaker($this->getBreaker(new NfgServiceUnavailableException()));

        try {
            $factory->createClient('invalid', true);
        } catch (\Exception $e) {
            $this->assertInstanceOf('PROCERGS\LoginCidadao\NfgBundle\Exception\NfgServiceUnavailableException', $e);
            $this->assertNull($e->getPrevious());
        }
    }

    public function testLogging()
    {
        $successFactory = $this->getValidFactory();

        /** @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject $logger */
        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $logger->expects($this->atLeastOnce())->method('info');
        $logger->expects($this->atLeastOnce())->method('error');

        $successFactory->setLogger($logger);
        $successFactory->createClient('valid', true);

        try {
            $failureFactory = new SoapClientFactory();
            $failureFactory->setLogger($logger);
            $failureFactory->createClient('invalid', true);
        } catch (\Exception $e) {
            // success
        }
    }

    /**
     * @return SoapClientFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getValidFactory()
    {
        /** @var SoapClientFactory|\PHPUnit_Framework_MockObject_MockObject $factory */
        $factory = $this->getMockBuilder('PROCERGS\LoginCidadao\NfgBundle\Service\SoapClientFactory')
            ->setMethods(['instantiateSoapClient'])
            ->getMock();
        $factory->expects($this->atLeastOnce())
            ->method('instantiateSoapClient')
            ->willReturn($this->getMockBuilder('\SoapClient')->disableOriginalConstructor()->getMock());
        $factory->setCircuitBreaker($this->getBreaker());

        return $factory;
    }
}
