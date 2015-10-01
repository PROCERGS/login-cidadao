<?php

namespace LoginCidadao\OpenIDBundle\Controller;

use FOS\OAuthServerBundle\Event\OAuthEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class AuthorizeController extends Controller
{

    /**
     * @Route("/openid/connect/authorize", name="_authorize_handle")
     * @Method({"POST"})
     */
    public function handleAuthorizeAction(Request $request)
    {
        $scope         = $request->request->get('scope');
        $is_authorized = $request->request->has('rejected') === false || $request->request->has('accepted')
            === true;
        $request->request->set('scope', implode(' ', $scope));

        $server = $this->get('oauth2.server');

        $response = $server->handleAuthorizeRequest($this->get('oauth2.request'),
            $this->get('oauth2.response'), $is_authorized,
            $this->getUser()->getId());

        $id     = explode('_', $request->get('client_id'));
        $em     = $this->getDoctrine()->getManager();
        $client = $em->getRepository('PROCERGSOAuthBundle:Client')->find($id[0]);
        $event  = new OAuthEvent($this->getUser(), $client, $is_authorized);
        $this->get('event_dispatcher')->dispatch(OAuthEvent::POST_AUTHORIZATION_PROCESS,
            $event);

        return $response;
    }

    /**
     * @Template()
     */
    public function authorizeAction($client_id, $scope, $response_type,
                                    $redirect_uri, $state = null, $nonce = null)
    {
        $id     = explode('_', $client_id);
        $em     = $this->getDoctrine()->getManager();
        $client = $em->getRepository('PROCERGSOAuthBundle:Client')
            ->find($id[0]);

        $scope = explode(' ', $scope);
        if (array_search('public_profile', $scope) === false) {
            $scope[] = 'public_profile';
        }

        $scopeManager = $this->getScopeManager();
        $scopes       = $scopeManager->findScopesByScopes($scope);

        $qs = compact('client_id', 'scope', 'response_type', 'redirect_uri',
            'state', 'nonce');
        return compact('qs', 'scopes', 'client');
    }

    /**
     * @return \OAuth2\ServerBundle\Manager\ScopeManager
     */
    private function getScopeManager()
    {
        return $this->get('oauth2.scope_manager');
    }
}
