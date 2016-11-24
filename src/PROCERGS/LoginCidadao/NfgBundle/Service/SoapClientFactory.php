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
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class SoapClientFactory implements LoggerAwareInterface
{
    use CircuitBreakerAwareTrait;

    /** @var LoggerInterface */
    private $logger;

    public function createClient($wsdl, $verifyHttps = true)
    {
        try {
            $this->checkAvailable();

            $client = $this->instantiateSoapClient($wsdl, $this->getOptions($verifyHttps));
            $this->reportSuccess();

            return $client;
        } catch (\SoapFault $e) {
            $this->reportFailure($e);
            throw new NfgServiceUnavailableException($e->getMessage(), 500, $e);
        }
    }

    public function instantiateSoapClient($wsdl, $options)
    {
        return @new \SoapClient($wsdl, $options);
    }

    /**
     * @codeCoverageIgnore
     */
    private function getOptions($verifyHttps)
    {
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

        return $options;
    }

    /**
     * Sets a logger instance on the object.
     *
     * @param LoggerInterface $logger
     *
     * @return void
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
