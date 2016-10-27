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
use FOS\UserBundle\Security\LoginManagerInterface;
use LoginCidadao\CoreBundle\Entity\Person;
use PROCERGS\LoginCidadao\CoreBundle\Entity\PersonMeuRS;
use PROCERGS\LoginCidadao\NfgBundle\Exception\NfgServiceUnavailableException;
use PROCERGS\LoginCidadao\NfgBundle\Helper\UrlHelper;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;

class Nfg
{
    /**
     * Key used to store the NFG AccessID in session
     */
    const ACCESS_ID_SESSION_KEY = 'nfg.access_id';

    /** @var NfgSoapInterface */
    private $nfgSoap;

    /** @var RouterInterface */
    private $router;

    /** @var SessionInterface */
    private $session;

    /** @var LoginManagerInterface */
    private $loginManager;

    /** @var CircuitBreakerInterface */
    private $circuitBreaker;

    /**
     * This service's name on the Circuit Breaker
     * @var string
     */
    private $cbServiceName;

    /** @var string */
    private $loginEndpoint;

    /** @var string */
    private $firewallName;

    public function __construct(
        NfgSoapInterface $client,
        RouterInterface $router,
        SessionInterface $session,
        LoginManagerInterface $loginManager,
        $firewallName,
        $loginEndpoint
    ) {
        $this->nfgSoap = $client;
        $this->router = $router;
        $this->session = $session;
        $this->loginManager = $loginManager;
        $this->firewallName = $firewallName;
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
     * @throws NfgServiceUnavailableException
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
        $this->session->set(self::ACCESS_ID_SESSION_KEY, $accessId);
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
        $url = http_build_url($url);

        // TODO: remove this after NFG gets its bugs fixed
        return new Response(
            '<html><head><meta name="referrer" content="always"/></head><body><script type="text/javascript">document.location= "'.$url.'";</script></body></html>'
        );
        //return new RedirectResponse(http_build_url($url));
    }

    public function loginCallback(
        array $params,
        $secret
    ) {
        $cpf = array_key_exists('cpf', $params) ? $params['cpf'] : null;
        $accessId = array_key_exists('accessId', $params) ? $params['accessId'] : null;
        $prsec = array_key_exists('prsec', $params) ? $params['prsec'] : null;

        if (!$cpf || !$accessId || !$prsec) {
            throw new BadRequestHttpException('Missing CPF, AccessID or PRSEC');
        }

        $signature = hash_hmac('sha256', "$cpf$accessId", $secret);
        if (!$signature || strcmp(strtolower($signature), strtolower($prsec)) !== 0) {
            throw new AccessDeniedHttpException('Invalid PRSEC signature.');
        }

        if ($this->session->get(self::ACCESS_ID_SESSION_KEY) !== $accessId) {
            throw new AccessDeniedHttpException('Invalid AccessID');
        }

        // TODO: find user by $cpf
        $user = new Person();
        $personMeuRS = new PersonMeuRS();
        $personMeuRS->setNfgAccessToken('dummy');

        if (!$user || !$personMeuRS->getNfgAccessToken()) {
            throw new NotFoundHttpException('No user found matching this CPF');
        }

        $response = new RedirectResponse($this->router->generate('lc_home'));
        $this->loginManager->logInUser($this->firewallName, $user, $response);

        return $response;
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
