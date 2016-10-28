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

use FOS\UserBundle\Security\LoginManagerInterface;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use PROCERGS\LoginCidadao\CoreBundle\Entity\PersonMeuRS;
use PROCERGS\LoginCidadao\CoreBundle\Helper\MeuRSHelper;
use PROCERGS\LoginCidadao\NfgBundle\Exception\NfgServiceUnavailableException;
use PROCERGS\LoginCidadao\NfgBundle\Helper\UrlHelper;
use PROCERGS\LoginCidadao\NfgBundle\Traits\CircuitBreakerAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccountStatusException;

class Nfg implements LoggerAwareInterface
{
    use CircuitBreakerAwareTrait {
        reportSuccess as traitReportSuccess;
        reportFailure as traitReportFailure;
    }

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

    /** @var MeuRSHelper */
    private $meuRSHelper;

    /** @var LoggerInterface */
    private $logger;

    /** @var string */
    private $loginEndpoint;

    /** @var string */
    private $authorizationEndpoint;

    /** @var string */
    private $firewallName;

    public function __construct(
        NfgSoapInterface $client,
        RouterInterface $router,
        SessionInterface $session,
        LoginManagerInterface $loginManager,
        MeuRSHelper $meuRSHelper,
        $firewallName,
        $loginEndpoint,
        $authorizationEndpoint
    ) {
        $this->nfgSoap = $client;
        $this->router = $router;
        $this->session = $session;
        $this->loginManager = $loginManager;
        $this->meuRSHelper = $meuRSHelper;
        $this->firewallName = $firewallName;
        $this->loginEndpoint = $loginEndpoint;
        $this->authorizationEndpoint = $authorizationEndpoint;
    }

    /**
     * @return string
     * @throws NfgServiceUnavailableException
     */
    private function getAccessId()
    {
        if (false === $this->isAvailable()) {
            throw new NfgServiceUnavailableException('NFG service is unavailable right now. Try again later.');
        }

        try {
            $accessId = $this->nfgSoap->getAccessID();
            $this->reportSuccess();

            return $accessId;
        } catch (NfgServiceUnavailableException $e) {
            $this->reportFailure($e);
            throw $e;
        } catch (\Exception $e) {
            $this->reportFailure($e);
            throw new NfgServiceUnavailableException($e->getMessage(), 500, $e);
        }
    }

    /**
     * @param string $accessToken
     * @param string|null $voterRegistration
     * @return \PROCERGS\LoginCidadao\CoreBundle\Entity\NfgProfile
     */
    private function getUserInfo($accessToken, $voterRegistration = null)
    {
        if (false === $this->isAvailable()) {
            throw new NfgServiceUnavailableException('NFG service is unavailable right now. Try again later.');
        }

        try {
            $nfgProfile = $this->nfgSoap->getUserInfo($accessToken, $voterRegistration);
            $this->reportSuccess();

            if (array_search(null, [$nfgProfile->getEmail(), $nfgProfile->getCpf(), $nfgProfile->getName()])) {
                // TODO: throw missing required info exception
            }

            return $nfgProfile;
        } catch (NfgServiceUnavailableException $e) {
            $this->reportFailure($e);
            throw $e;
        } catch (\Exception $e) {
            $this->reportFailure($e);
            throw new NfgServiceUnavailableException($e->getMessage(), 500, $e);
        }
    }

    /**
     * @return JsonResponse
     */
    public function login()
    {
        return $this->redirect($this->loginEndpoint, 'nfg_login_callback');
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

        /** @var PersonInterface $user */
        $personMeuRS = $this->meuRSHelper->getPersonByCpf($this->sanitizeCpf($cpf));
        $personMeuRS->setNfgAccessToken('dummy');
        $user = $personMeuRS->getPerson();

        if (!$user || !$personMeuRS->getNfgAccessToken()) {
            throw new NotFoundHttpException('No user found matching this CPF');
        }

        $response = new RedirectResponse($this->router->generate('lc_home'));

        try {
            $this->loginManager->logInUser($this->firewallName, $user, $response);
        } catch (AccountStatusException $e) {
            // User account is disabled or something like that
            throw $e;
        }

        return $response;
    }

    public function connect()
    {
        return $this->redirect($this->authorizationEndpoint, 'nfg_connect_callback');
    }

    /**
     * @param PersonMeuRS $personMeuRS
     * @param string $paccessId
     * @return RedirectResponse
     */
    public function connectCallback(PersonMeuRS $personMeuRS, $paccessId)
    {
        // TODO: check access token
        if (!$paccessId) {
            throw new BadRequestHttpException("Missing paccessid parameter");
        }

        $nfgProfile = $this->getUserInfo($paccessId, $personMeuRS->getVoterRegistration());

        // TODO: check Person CPF

        // TODO: check CPF collision

        // TODO: save NfgProfile

        // TODO: link NfgProfile to PersonMeuRS
        // TODO: save AccessToken to PersonMeuRS
        // TODO: redirect to Profile?
        return new RedirectResponse($this->router->generate('lc_home'));
    }

    private function redirect($endpoint, $callbackRoute)
    {
        $accessId = $this->getAccessId();
        $this->session->set(self::ACCESS_ID_SESSION_KEY, $accessId);
        $callbackUrl = $this->router->generate($callbackRoute, [], RouterInterface::ABSOLUTE_URL);

        $url = parse_url($endpoint);
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
//        return new Response(
//            '<html><head><meta name="referrer" content="always"/></head><body><script type="text/javascript">document.location= "'.$url.'";</script></body></html>'
//        );

        return new JsonResponse(['target' => $url]);
        //return new RedirectResponse(http_build_url($url));
    }

    protected function reportSuccess()
    {
        $this->traitReportSuccess();

        if ($this->logger instanceof LoggerInterface) {
            $this->logger->info('NFG service reported success');
        }
    }

    protected function reportFailure(\Exception $e = null)
    {
        $this->traitReportFailure();

        if ($e && $this->logger instanceof LoggerInterface) {
            $this->logger->error("NFG reported failure: {$e->getMessage()}");
        }
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

    private function sanitizeCpf($cpf)
    {
        return str_pad($cpf, 11, '0', STR_PAD_LEFT);
    }
}
