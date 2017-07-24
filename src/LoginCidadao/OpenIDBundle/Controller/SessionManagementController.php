<?php
/*
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Controller;

use LoginCidadao\CoreBundle\Helper\SecurityHelper;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use LoginCidadao\OpenIDBundle\Entity\ClientMetadata;
use LoginCidadao\OpenIDBundle\Entity\ClientMetadataRepository;
use LoginCidadao\OpenIDBundle\Exception\IdTokenSubMismatchException;
use LoginCidadao\OpenIDBundle\Exception\IdTokenValidationException;
use LoginCidadao\OpenIDBundle\Form\EndSessionForm;
use LoginCidadao\OpenIDBundle\Service\SubjectIdentifierService;
use LoginCidadao\OpenIDBundle\Storage\PublicKey;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @Route("/openid/connect")
 */
class SessionManagementController extends Controller
{

    /**
     * @Route("/session/check", name="oidc_check_session_iframe")
     * @Method({"GET"})
     * @Template
     */
    public function checkSessionAction(Request $request)
    {
        return array();
    }

    /**
     * @Route("/session/origins", name="oidc_get_origins")
     * @Method({"GET"})
     * @Template
     */
    public function getOriginsAction(Request $request)
    {
        $client = $this->getClient($request->get('client_id'));

        $uris = array();
        $uris[] = $client->getSiteUrl();
        $uris[] = $client->getTermsOfUseUrl();
        $uris[] = $client->getLandingPageUrl();
        if ($client->getMetadata()) {
            $meta = $client->getMetadata();
            $uris[] = $meta->getClientUri();
            $uris[] = $meta->getInitiateLoginUri();
            $uris[] = $meta->getPolicyUri();
            $uris[] = $meta->getSectorIdentifierUri();
            $uris[] = $meta->getTosUri();
            $uris = array_merge(
                $uris,
                $meta->getRedirectUris(),
                $meta->getRequestUris()
            );
        }

        $result = array_unique(
            array_map(
                function ($value) {
                    if ($value === null) {
                        return;
                    }
                    $uri = parse_url($value);

                    $uri['fragment'] = '';
                    $uri['path'] = '';
                    $uri['query'] = '';
                    $uri['user'] = '';
                    $uri['pass'] = '';

                    return $this->unparseUrl($uri);
                },
                array_filter($uris)
            )
        );

        return new JsonResponse(array_values($result));
    }

    /**
     * @Route("/session/end", name="oidc_end_session_endpoint")
     * @Template
     */
    public function endSessionAction(Request $request)
    {
        $alwaysGetRedirectConsent = $this->alwaysGetRedirectConsent();

        $view = 'LoginCidadaoOpenIDBundle:SessionManagement:endSession.html.twig';
        $finishedView = 'LoginCidadaoOpenIDBundle:SessionManagement:endSession.finished.html.twig';
        try {
            $idToken = $request->get('id_token_hint');
            $postLogoutUri = $request->get('post_logout_redirect_uri', null);
            $loggedOut = !$this->isGranted('IS_AUTHENTICATED_REMEMBERED');
            try {
                $getLogoutConsent = $this->shouldGetLogoutConsent($idToken, $loggedOut);
            } catch (IdTokenSubMismatchException $e) {
                $getLogoutConsent = true;
            }

            list($postLogoutUri, $postLogoutHost) = $this->getPostLogoutInfo($request, $postLogoutUri, $idToken);
        } catch (IdTokenValidationException $e) {
            return $this->render($finishedView, ['error' => 'openid.session.end.invalid_id_token']);
        }

        $getRedirectConsent = $alwaysGetRedirectConsent && $postLogoutUri;
        $authorizedRedirect = !$getLogoutConsent && !$getRedirectConsent;
        $authorizedLogout = !$getLogoutConsent;
        $formChecked = false;

        $form = $this->createForm(
            new EndSessionForm(),
            ['logout' => true, 'redirect' => true],
            [
                'getLogoutConsent' => $getLogoutConsent,
                'getRedirectConsent' => $getRedirectConsent,
            ]
        );
        $form->handleRequest($request);
        if ($form->isValid()) {
            $data = $form->getData();

            $authorizedRedirect = false === $getRedirectConsent || $data['redirect'];
            $authorizedLogout = false === $getLogoutConsent || $data['logout'];
            $formChecked = true;
        }

        $params = [
            'form' => $form->createView(),
            'client' => $this->getLogoutClient($idToken),
            'postLogoutUri' => $postLogoutUri,
            'postLogoutHost' => $postLogoutHost,
            'getLogoutConsent' => $getLogoutConsent,
            'getRedirectConsent' => $getRedirectConsent,
            'loggedOut' => $loggedOut,
        ];

        if (($getLogoutConsent || $getRedirectConsent)
            && !$authorizedRedirect
            && $formChecked
        ) {
            $view = $finishedView;
        }

        $response = null;
        if ($postLogoutUri && $authorizedRedirect) {
            $response = $this->redirect($postLogoutUri);
        }

        if ($authorizedLogout && !$loggedOut) {
            if (!$response) {
                $params['loggedOut'] = true;
                $response = $this->render($view, $params);
            }
            $response = $this->getSecurityHelper()->logout($request, $response);
        }

        return $response ?: $this->render($view, $params);
    }

    /**
     * @param string $clientId
     * @return \LoginCidadao\OAuthBundle\Entity\Client|object
     */
    private function getClient($clientId)
    {
        $clientId = explode('_', $clientId);
        $id = $clientId[0];

        return $this->getDoctrine()->getManager()
            ->getRepository('LoginCidadaoOAuthBundle:Client')->find($id);
    }

    private function unparseUrl($parsed_url)
    {
        $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'].'://' : '';
        $host = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port = isset($parsed_url['port']) ? ':'.$parsed_url['port'] : '';
        $user = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass = isset($parsed_url['pass']) ? ':'.$parsed_url['pass'] : '';
        $pass = ($user || $pass) ? "$pass@" : '';
        $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $query = isset($parsed_url['query']) ? '?'.$parsed_url['query'] : '';
        $fragment = isset($parsed_url['fragment']) ? '#'.$parsed_url['fragment']
            : '';

        return "$scheme$user$pass$host$port$path$query$fragment";
    }

    /**
     * @param mixed $idToken a JWT ID Token as a \JOSE_JWT object or string
     * @return bool true if $idToken is valid, false otherwise
     * @throws IdTokenSubMismatchException
     * @throws IdTokenValidationException
     */
    private function checkIdToken($idToken)
    {
        $idToken = $this->getIdToken($idToken);

        /** @var PublicKey $publicKeyStorage */
        $publicKeyStorage = $this->get('oauth2.storage.public_key');
        try {
            @$idToken->verify($publicKeyStorage->getPublicKey($idToken->claims['aud']));

            if (false === $this->checkIdTokenSub($this->getUser(), $idToken)) {
                throw new IdTokenSubMismatchException('Invalid subject identifier', Response::HTTP_BAD_REQUEST);
            }

            return true;
        } catch (IdTokenSubMismatchException $e) {
            throw $e;
        } catch (\JOSE_Exception_VerificationFailed $e) {
            throw new IdTokenValidationException($e->getMessage(), Response::HTTP_BAD_REQUEST, $e);
        } catch (\Exception $e) {
            throw new IdTokenValidationException($e->getMessage(), Response::HTTP_BAD_REQUEST, $e);
        }
    }

    /**
     * @param PersonInterface $person
     * @param mixed $idToken
     * @return bool
     */
    private function checkIdTokenSub(PersonInterface $person = null, $idToken)
    {
        if (null === $person) {
            // User is logged out
            return true;
        }

        if (!($person instanceof PersonInterface)) {
            return false;
        }

        $client = $this->getClient($idToken->claims['aud']);

        $sub = $this->getSubjectIdentifier($person, $client);

        return $idToken->claims['sub'] === $sub;
    }

    /**
     * Enforces that the ID Token is a \JOSE_JWT object
     * @param mixed $idToken
     * @return \JOSE_JWE|\JOSE_JWT
     */
    private function getIdToken($idToken)
    {
        if (!($idToken instanceof \JOSE_JWT)) {
            try {
                $idToken = \JOSE_JWT::decode($idToken);
            } catch (\JOSE_Exception_InvalidFormat $e) {
                throw new BadRequestHttpException($e->getMessage(), $e);
            }
        }

        return $idToken;
    }

    private function validatePostLogoutUri($postLogoutUri, $idToken)
    {
        if ($postLogoutUri === null) {
            return false;
        }

        $postLogoutUri = ClientMetadata::canonicalizeUri($postLogoutUri);

        if (!$idToken) {
            return count($this->findClientByPostLogoutRedirectUri($postLogoutUri)) > 0;
        }

        $idToken = $this->getIdToken($idToken);
        $client = $this->getClient($idToken->claims['aud']);

        return false !== array_search($postLogoutUri, $client->getMetadata()->getPostLogoutRedirectUris());
    }

    private function addStateToUri($postLogoutUri, $state)
    {
        if ($state) {
            $url = parse_url($postLogoutUri);
            if (array_key_exists('query', $url)) {
                parse_str($url['query'], $query);
            } else {
                $query = [];
            }
            $query['state'] = $state;
            $url['query'] = http_build_query($query);

            return $this->unparseUrl($url);
        } else {
            return $postLogoutUri;
        }
    }

    /**
     * @return bool
     */
    private function alwaysGetLogoutConsent()
    {
        return $this->getParameter('rp_initiated_logout.logout.always_get_consent');
    }

    /**
     * @return bool
     */
    private function alwaysGetRedirectConsent()
    {
        return $this->getParameter('rp_initiated_logout.redirect.always_get_consent');
    }

    /**
     * @param string|\JOSE_JWT $idToken
     * @return \LoginCidadao\OAuthBundle\Entity\Client|false
     */
    private function getIdTokenClient($idToken)
    {
        if ($idToken === null) {
            return false;
        }

        $idToken = $this->getIdToken($idToken);
        $client = $this->getClient($idToken->claims['aud']);

        return $client;
    }

    /**
     * @return SecurityHelper
     */
    private function getSecurityHelper()
    {
        /** @var SecurityHelper $securityHelper */
        $securityHelper = $this->get('lc.security.helper');

        return $securityHelper;
    }

    private function getSubjectIdentifier(PersonInterface $person, ClientInterface $client)
    {
        /** @var SubjectIdentifierService $service */
        $service = $this->get('oidc.subject_identifier.service');

        return $service->getSubjectIdentifier($person, $client->getMetadata());
    }

    private function findClientByPostLogoutRedirectUri($postLogoutUri)
    {
        /** @var ClientMetadataRepository $repo */
        $repo = $this->get('oidc.client_metadata.repository');

        return $repo->findByPostLogoutRedirectUri($postLogoutUri);
    }

    private function shouldGetLogoutConsent($idToken, $loggedOut)
    {
        $getLogoutConsent = $loggedOut ? false : $this->alwaysGetLogoutConsent();

        if ($idToken) {
            if (false === $this->checkIdToken($idToken)) {
                // We didn't receive a valid ID Token, therefore we should ask user for consent
                $getLogoutConsent = true;
            }
        }

        return $getLogoutConsent;
    }

    private function getLogoutClient($idToken)
    {
        $client = null;
        if ($idToken) {
            if ($this->checkIdToken($idToken)) {
                $client = $this->getIdTokenClient($idToken);
            }
        }

        return $client;
    }

    private function getPostLogoutInfo(Request $request, $postLogoutUri, $idToken)
    {
        $postLogoutHost = null;
        if ($this->validatePostLogoutUri($postLogoutUri, $idToken)) {
            $postLogoutUri = $this->addStateToUri($postLogoutUri, $request->get('state', null));
            $postLogoutHost = parse_url($postLogoutUri)['host'];
        } else {
            $postLogoutUri = null;
        }

        return [$postLogoutUri, $postLogoutHost];
    }
}
