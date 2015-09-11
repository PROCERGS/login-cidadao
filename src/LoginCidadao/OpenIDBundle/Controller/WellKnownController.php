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
        $authEndpoint  = $this->generateUrl('_authorize_validate', array(),
            UrlGeneratorInterface::ABSOLUTE_URL);
        $tokenEndpoint = $this->generateUrl('_token', array(),
            UrlGeneratorInterface::ABSOLUTE_URL);

        $data = array(
            'issuer' => 'https://'.$this->getParameter('site_domain'),
            'authorization_endpoint' => $authEndpoint,
            'token_endpoint' => $tokenEndpoint,
            'token_endpoint_auth_methods_supported' => array(
                "client_secret_basic", "private_key_jwt"
            ),
            'token_endpoint_auth_signing_alg_values_supported' => array(
                "RS256", "ES256"
            ),
            'userinfo_endpoint' => '',
            'check_session_iframe' => '',
            'end_session_endpoint' => '',
            'jwks_uri' => '',
            'registration_endpoint' => '',
            'scopes_supported' => '',
            'response_types_supported' => array(
                "code", "code id_token", "id_token", "token id_token"
            ),
            // ...
        );

        return new \Symfony\Component\HttpFoundation\JsonResponse($data);
    }
}
