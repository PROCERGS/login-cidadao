<?php

namespace LoginCidadao\OpenIDBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class WellKnownController extends Controller
{

    /**
     * @Route("/.well-known/openid-configuration", name="oidc_wellknown")
     * @Method({"GET"})
     */
    public function wellKnownAction()
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
            //'check_session_iframe' => '',
            //'end_session_endpoint' => '',
            'jwks_uri' => $jwksUri,
            'registration_endpoint' => $registrationEndpoint,
            'scopes_supported' => '',
            'response_types_supported' => array(
                "code", "code id_token", "id_token", "token id_token"
            ),
            'subject_types_supported' => array(
                'pairwise'
            )
        );

        return new \Symfony\Component\HttpFoundation\JsonResponse($data);
    }
}
