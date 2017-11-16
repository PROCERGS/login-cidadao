<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\Controller\Annotations as REST;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class WellKnownController extends FOSRestController
{

    /**
     * @REST\Get("/.well-known/openid-configuration", name="oidc_wellknown", defaults={"_format"="json"})
     * @REST\View(templateVar="oidc_config")
     */
    public function openidConfigAction()
    {
        $authEndpoint   = $this->generateUrl('_authorize_validate', array(),
            UrlGeneratorInterface::ABSOLUTE_URL);
        $tokenEndpoint  = $this->generateUrl('_token', array(),
            UrlGeneratorInterface::ABSOLUTE_URL);
        $personEndpoint = $this->generateUrl('get_person',
            array('_format' => 'json'), UrlGeneratorInterface::ABSOLUTE_URL);

        $registrationEndpoint = $this->generateUrl('oidc_dynamic_registration',
            array(), UrlGeneratorInterface::ABSOLUTE_URL);

        $jwksUri = $this->generateUrl('oidc_jwks', array(),
            UrlGeneratorInterface::ABSOLUTE_URL);

        $checkSessionEndpoint = $this->generateUrl('oidc_check_session_iframe',
            array(), UrlGeneratorInterface::ABSOLUTE_URL);
        $endSessionEndpoint   = $this->generateUrl('oidc_end_session_endpoint',
            array(), UrlGeneratorInterface::ABSOLUTE_URL);

        $data = array(
            'issuer' => $this->getParameter('jwt_iss'),
            'authorization_endpoint' => $authEndpoint,
            'token_endpoint' => $tokenEndpoint,
            'token_endpoint_auth_methods_supported' => array(
                "client_secret_basic", "private_key_jwt"
            ),
            'token_endpoint_auth_signing_alg_values_supported' => array(
                "RS256"//, "ES256"
            ),
            'id_token_signing_alg_values_supported' => array(
                'RS256'
            ),
            'userinfo_endpoint' => $personEndpoint,
            'check_session_iframe' => $checkSessionEndpoint,
            'end_session_endpoint' => $endSessionEndpoint,
            'jwks_uri' => $jwksUri,
            'registration_endpoint' => $registrationEndpoint,
            'scopes_supported' => explode(' ',
                $this->getParameter('lc_supported_scopes')),
            'response_types_supported' => array(
                "code", "code id_token", "id_token", "token id_token"
            ),
            'subject_types_supported' => array(
                'pairwise'
            )
        );

        return new JsonResponse($data);
    }

    /**
     * @REST\Get("/.well-known/webfinger", name="oidc_provider_discovery", defaults={"_format"="json"})
     * @REST\View(templateVar="webfinger")
     */
    public function webFingerAction(Request $request)
    {
        $rel = $request->get('rel');

        if ($rel !== 'http://openid.net/specs/connect/1.0/issuer') {
            throw new BadRequestHttpException('Unsupported "rel" value.');
        }

        $resource = $this->validateSubjectResource($request->get('resource'));

        $response = array(
            'subject' => $resource,
            'links' => array(
                array(
                    'rel' => $rel,
                    'href' => $this->getParameter('jwt_iss')
                )
            )
        );

        return new JsonResponse($response);
    }

    private function validateSubjectResource($resource)
    {
        if (strpos($resource, 'acct:') === 0) {
            $parseable = preg_replace('/^acct:/', 'acct://', $resource, 1);
        } else {
            $parseable = $resource;
        }

        $default = array(
            'scheme' => null,
            'user' => null,
            'host' => null,
        );

        $parts = array_merge($default, parse_url($parseable));

        if ($parts['scheme'] === null) {
            if ($parts['host'] !== null && $parts['user'] !== null) {
                $parts['scheme'] = 'acct';
            } else {
                $parts['scheme'] = 'https';
            }
        }

        if ($parts['scheme'] === null || $parts['host'] === null) {
            throw new BadRequestHttpException("Invalid resource");
        }

        return $resource;
    }
}
