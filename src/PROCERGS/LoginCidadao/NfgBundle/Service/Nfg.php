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

use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Form\Factory\FormFactory;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Security\LoginManagerInterface;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use PROCERGS\LoginCidadao\CoreBundle\Entity\NfgProfile;
use PROCERGS\LoginCidadao\CoreBundle\Entity\PersonMeuRS;
use PROCERGS\LoginCidadao\CoreBundle\Helper\MeuRSHelper;
use PROCERGS\LoginCidadao\NfgBundle\Exception\ConnectionNotFoundException;
use PROCERGS\LoginCidadao\NfgBundle\Exception\CpfInUseException;
use PROCERGS\LoginCidadao\NfgBundle\Exception\CpfMismatchException;
use PROCERGS\LoginCidadao\NfgBundle\Exception\EmailInUseException;
use PROCERGS\LoginCidadao\NfgBundle\Exception\MissingRequiredInformationException;
use PROCERGS\LoginCidadao\NfgBundle\Exception\NfgAccountCollisionException;
use PROCERGS\LoginCidadao\NfgBundle\Exception\NfgServiceUnavailableException;
use PROCERGS\LoginCidadao\NfgBundle\Exception\OverrideResponseException;
use PROCERGS\LoginCidadao\NfgBundle\Helper\UrlHelper;
use PROCERGS\LoginCidadao\NfgBundle\Traits\CircuitBreakerAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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

    /** @var EntityManager */
    private $em;

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

    /** @var UserManagerInterface */
    private $userManager;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var FormFactory */
    private $formFactory;

    public function __construct(
        EntityManager $em,
        NfgSoapInterface $client,
        RouterInterface $router,
        SessionInterface $session,
        LoginManagerInterface $loginManager,
        MeuRSHelper $meuRSHelper,
        EventDispatcherInterface $dispatcher,
        UserManagerInterface $userManager,
        FormFactory $formFactory,
        $firewallName,
        $loginEndpoint,
        $authorizationEndpoint
    ) {
        $this->em = $em;
        $this->nfgSoap = $client;
        $this->router = $router;
        $this->session = $session;
        $this->loginManager = $loginManager;
        $this->meuRSHelper = $meuRSHelper;
        $this->dispatcher = $dispatcher;
        $this->userManager = $userManager;
        $this->formFactory = $formFactory;
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

            $requiredInfo = [$nfgProfile->getCpf(), $nfgProfile->getBirthdate()];
            $missingRequiredInfo = array_search(
                null,
                $requiredInfo
            );
            if (false !== $missingRequiredInfo) {
                throw new MissingRequiredInformationException('Some needed information was not authorized on NFG.');
            }

            return $nfgProfile;
        } catch (NfgServiceUnavailableException $e) {
            $this->reportFailure($e);
            throw $e;
        } catch (MissingRequiredInformationException $e) {
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

    public function loginCallback(array $params, $secret)
    {
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

        if (!$personMeuRS || !$personMeuRS->getPerson() || !$personMeuRS->getNfgAccessToken()) {
            throw new ConnectionNotFoundException('No user found matching this CPF');
        }
        $user = $personMeuRS->getPerson();

        $response = new RedirectResponse($this->router->generate('lc_home'));

        try {
            $this->loginManager->logInUser($this->firewallName, $user, $response);
        } catch (AccountStatusException $e) {
            // User account is disabled or something like that
            throw $e;
        }

        // TODO: trigger event to allow response to be changed
        return $response;
    }

    public function connect()
    {
        return $this->redirect($this->authorizationEndpoint, 'nfg_connect_callback');
    }

    /**
     * @param Request $request
     * @param PersonMeuRS $personMeuRS
     * @param bool $overrideExisting
     * @return RedirectResponse
     */
    public function connectCallback(Request $request, PersonMeuRS $personMeuRS, $overrideExisting = false)
    {
        $response = null;
        $accessToken = $request->get('paccessid');
        if (!$accessToken) {
            throw new BadRequestHttpException("Missing paccessid parameter");
        }

        $nfgProfile = $this->getUserInfo($accessToken, $personMeuRS->getVoterRegistration());

        if (!($personMeuRS->getPerson() instanceof PersonInterface)) {
            try {
                $response = $this->register($request, $personMeuRS, $nfgProfile);
            } catch (OverrideResponseException $e) {
                return $e->getResponse();
            }
        }

        $sanitizedCpf = $this->sanitizeCpf($nfgProfile->getCpf());
        if (!$personMeuRS->getPerson()->getCpf()) {
            $personMeuRS->getPerson()->setCpf($sanitizedCpf);
        }

        $this->checkCpf($personMeuRS, $nfgProfile, $overrideExisting);

        // TODO: check duplicate NfgProfile already persisted
        $this->em->persist($nfgProfile);
        $personMeuRS->setNfgProfile($nfgProfile);
        $personMeuRS->setNfgAccessToken($accessToken);
        $this->em->flush($nfgProfile);
        $this->em->flush($personMeuRS);

        // TODO: trigger event to allow response to be changed
        if ($response) {
            return $response;
        }

        return new RedirectResponse($this->router->generate('fos_user_profile_edit'));
    }

    /**
     * @param PersonMeuRS $personMeuRS
     * @return RedirectResponse
     */
    public function disconnect(PersonMeuRS $personMeuRS)
    {
        $this->em->remove($personMeuRS->getNfgProfile());
        $personMeuRS->setNfgAccessToken(null);
        $personMeuRS->setNfgProfile(null);
        $this->em->flush();

        // TODO: trigger event to allow response to be changed
        return new RedirectResponse($this->router->generate('fos_user_profile_edit'));
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
        return str_pad(preg_replace('/[^0-9]/', '', $cpf), 11, '0', STR_PAD_LEFT);
    }

    /**
     * @param PersonMeuRS $personMeuRS
     * @param NfgProfile $nfgProfile
     * @param bool $overrideExisting
     */
    private function checkCpf(PersonMeuRS $personMeuRS, NfgProfile $nfgProfile, $overrideExisting = false)
    {
        $person = $personMeuRS->getPerson();

        // Check data inconsistency
        if ($person->getCpf() !== $this->sanitizeCpf($nfgProfile->getCpf())) {
            // TODO: throw exception and redirect to edit CPF
            throw new CpfMismatchException("User's CPF doesn't match CPF from NFG.");
        }

        // Check CPF collision
        $otherPerson = $this->meuRSHelper->getPersonByCpf($person->getCpf());
        if (null === $otherPerson || $otherPerson->getId() === $personMeuRS->getId()) {
            // No collision found. We're good! :)
            return;
        }

        if (!$otherPerson->getNfgProfile()) {
            // The other person isn't linked with NFG, so $person can safely get the CPF
            $otherPerson->getPerson()->setCpf(null);

            // TODO: send email to $otherPerson about this

            return;
        }

        // Both users are linked to the same NFG account
        // What should we do?
        if (false === $overrideExisting) {
            throw new NfgAccountCollisionException();
        }
        // The user's choice was to remove the previous connection and use this new one
        // TODO: send email to $otherPerson about this
        $this->disconnect($otherPerson);
    }

    /**
     * @param NfgProfile $nfgProfile
     * @return null|RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    private function register(Request $request, PersonMeuRS $personMeuRS, NfgProfile $nfgProfile)
    {
        $sanitizedCpf = $this->sanitizeCpf($nfgProfile->getCpf());

        if ($this->meuRSHelper->getPersonByCpf($sanitizedCpf) !== null) {
            throw new CpfInUseException();
        }

        if ($this->meuRSHelper->getPersonByEmail($nfgProfile->getEmail()) !== null) {
            throw new EmailInUseException();
        }

        $names = explode(' ', $nfgProfile->getName());

        /** @var PersonInterface $user */
        $user = $this->userManager->createUser();
        $user
            ->setFirstName(array_shift($names))
            ->setSurname(implode(' ', $names))
            ->setEmail($nfgProfile->getEmail())
            ->setCpf($sanitizedCpf)
            ->setBirthdate($nfgProfile->getBirthdate())
            ->setMobile($nfgProfile->getMobile())
            ->setPassword('')
            ->setEnabled(true);

        $event = new GetResponseUserEvent($user, $request);
        $this->dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            throw new OverrideResponseException($event->getResponse());
        }

        $form = $this->formFactory->createForm();
        $form->setData($user);

        $event = new FormEvent($form, $request);
        $this->dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);

        $this->userManager->updateUser($user);

        $personMeuRS->setPerson($user);
        $personMeuRS->setNfgProfile($nfgProfile);
        $this->em->persist($personMeuRS);
        $this->em->persist($nfgProfile);
        $this->em->flush();

        if (null === $response = $event->getResponse()) {
            $url = $this->router->generate('fos_user_registration_confirmed');
            $response = new RedirectResponse($url);
        }

        $this->dispatcher->dispatch(
            FOSUserEvents::REGISTRATION_COMPLETED,
            new FilterUserResponseEvent($user, $request, $response)
        );

        return $response;
    }
}
