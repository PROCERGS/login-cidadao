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
use LoginCidadao\OpenIDBundle\Form\EndSessionForm;
use LoginCidadao\OpenIDBundle\Service\SubjectIdentifierService;
use LoginCidadao\OpenIDBundle\Storage\PublicKey;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

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
        $view = 'LoginCidadaoOpenIDBundle:SessionManagement:endSession.html.twig';
        $idToken = $request->get('id_token_hint');
        $postLogoutUri = $request->get('post_logout_redirect_uris', null);

        $alwaysGetLogoutConsent = $this->alwaysGetLogoutConsent();
        $alwaysGetRedirectConsent = $this->alwaysGetRedirectConsent();
        if ($idToken && false === $this->checkIdToken($idToken)) {
            // TODO: ask consent or report error (possible attack)?
            die("invalid ID Token");
        }
        $client = $this->getIdTokenClient($idToken);

        $validatedPostLogoutUri = $this->validatePostLogoutUri($postLogoutUri, $idToken);
        $postLogoutHost = null;
        if ($postLogoutUri) {
            $postLogoutUri = $this->addStateToUri($postLogoutUri, $request->get('state', null));
            $postLogoutHost = parse_url($postLogoutUri)['host'];
        }

        if ($alwaysGetRedirectConsent && $postLogoutUri) {
            $getRedirectConsent = true;
        } else {
            $getRedirectConsent = false;
        }

        $form = $this->createForm(
            new EndSessionForm(),
            ['logout' => true, 'redirect' => true],
            [
                'getLogoutConsent' => $alwaysGetLogoutConsent,
                'getRedirectConsent' => $getRedirectConsent,
            ]
        );
        $form->handleRequest($request);
        if ($form->isValid()) {
            $data = $form->getData();

            if (false === $getRedirectConsent || $data['redirect']) {
                $response = $this->redirect($postLogoutUri);
            } else {
                $response = $this->redirectToRoute('lc_home');
            }
            if (false === $alwaysGetLogoutConsent || $data['logout']) {
                $this->getSecurityHelper()->logout($request, $response);
            }

            return $response;
        }

        $params = [
            'form' => $form->createView(),
            'client' => $client,
            'postLogoutUri' => $postLogoutUri,
            'postLogoutHost' => $postLogoutHost,
            'alwaysGetLogoutConsent' => $alwaysGetLogoutConsent,
            'getRedirectConsent' => $getRedirectConsent,
        ];

        if (false === $alwaysGetLogoutConsent) {
            $params['loggedOut'] = true;
            $response = $this->render($view, $params);
            $response = $this->getSecurityHelper()->logout($request, $response);
        } else {
            $params['loggedOut'] = false;
            $response = $this->render($view, $params);
        }

        return $response;
    }

    /**
     * @param string $clientId
     * @return \LoginCidadao\OAuthBundle\Entity\Client
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
     */
    private function checkIdToken($idToken)
    {
        $idToken = $this->getIdToken($idToken);

        /** @var PublicKey $publicKeyStorage */
        $publicKeyStorage = $this->get('oauth2.storage.public_key');
        try {
            @$idToken->verify($publicKeyStorage->getPublicKey($idToken->claims['aud']));

            $person = $this->getUser();
            if (!($person instanceof PersonInterface)) {
                return false;
            }

            $client = $this->getClient($idToken->claims['aud']);

            return $idToken->claims['sub'] === $this->getSubjectIdentifier($person, $client);
        } catch (\JOSE_Exception_VerificationFailed $e) {
            // TODO: ask consent or report error (possible attack)?
            die("invalid ID Token!");
        } catch (\Exception $e) {
            // TODO: ask consent or report error (possible attack)?
            die("other error");
        }
    }

    /**
     * Enforces that the ID Token is a \JOSE_JWT object
     * @param mixed $idToken
     * @return \JOSE_JWE|\JOSE_JWT
     */
    private function getIdToken($idToken)
    {
        if (!($idToken instanceof \JOSE_JWT)) {
            $idToken = \JOSE_JWT::decode($idToken);
        }

        return $idToken;
    }

    private function validatePostLogoutUri($postLogoutUri, $idToken)
    {
        if ($idToken === null || $postLogoutUri === null) {
            return false;
        }

        $idToken = $this->getIdToken($idToken);
        $client = $this->getClient($idToken->claims['aud']);

        // TODO: get client's allowed post_logout_uri and check if it contains $postLogoutUri

        return true;
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

    private function rememberMe(Request $request)
    {
        $rememberMeCookieName = $this->getParameter('session.remember_me.name');

        return $request->cookies->has($rememberMeCookieName);
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
     * @return \LoginCidadao\OAuthBundle\Entity\Client
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
        return $this->get('lc.security.helper');
    }

    private function getSubjectIdentifier(PersonInterface $person, ClientInterface $client)
    {
        /** @var SubjectIdentifierService $service */
        $service = $this->get('oidc.subject_identifier.service');

        return $service->getSubjectIdentifier($person, $client->getMetadata());
    }
}
