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
use PROCERGS\LoginCidadao\NfgBundle\Helper\UrlHelper;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

class Nfg
{
    /** @var NfgSoapInterface */
    private $nfgSoap;

    /** @var RouterInterface */
    private $router;

    /** @var CircuitBreakerInterface */
    private $circuitBreaker;

    /**
     * This service's name on the Circuit Breaker
     * @var string
     */
    private $cbServiceName;

    /** @var string */
    private $loginEndpoint;

    public function __construct(
        NfgSoapInterface $client,
        RouterInterface $router,
        $loginEndpoint
    ) {
        $this->nfgSoap = $client;
        $this->router = $router;
        $this->loginEndpoint = $loginEndpoint;
    }

    /**
     * @param CircuitBreakerInterface|null $circuitBreaker
     * @param null $serviceName
     * @return Nfg
     */
    public function setCircuitBreaker(CircuitBreakerInterface $circuitBreaker = null, $serviceName = null)
    {
        $this->circuitBreaker = $circuitBreaker;
        $this->cbServiceName = $serviceName;

        return $this;
    }

    /**
     * @return string
     */
    private function getAccessId()
    {
        if ($this->circuitBreaker && false === $this->circuitBreaker->isAvailable($this->cbServiceName)) {
            throw new NfgServiceUnavailableException('NFG service is unavailable right now. Try again later.');
        }

        try {
            $accessId = $this->nfgSoap->getAccessID();
            $this->reportSuccess();

            return $accessId;
        } catch (\Exception $e) {
            $this->reportFailure();
            throw new NfgServiceUnavailableException($e->getMessage(), 500, $e);
        }
    }

    /**
     * @return RedirectResponse
     */
    public function login()
    {
        $accessId = $this->getAccessId();
        $callbackUrl = $this->router->generate('nfg_callback', [], RouterInterface::ABSOLUTE_URL);

        $url = parse_url($this->loginEndpoint);
        $url['query'] = UrlHelper::addToQuery(
            [
                'accessid' => $accessId,
                'urlretorno' => $callbackUrl,
            ],
            isset($url['query']) ? $url['query'] : null
        );

        // NFG has some bug that causes the application to fail if a Referrer is not present
        // So I'll have to do the redirect in this very ugly manner until this problem gets fixed.
        // TODO: remove this after NFG gets its bugs fixed
        return new Response(
            '<html><head><meta name="referrer" content="always"/></head><body><script type="text/javascript">document.location= "'.http_build_url($url).'";</script></body></html>'
        );
        //return new RedirectResponse(http_build_url($url));
    }

    private function reportSuccess()
    {
        if ($this->circuitBreaker && $this->cbServiceName) {
            $this->circuitBreaker->reportSuccess($this->cbServiceName);
        }
    }

    private function reportFailure()
    {
        if ($this->circuitBreaker && $this->cbServiceName) {
            $this->circuitBreaker->reportFailure($this->cbServiceName);
        }
    }
}
