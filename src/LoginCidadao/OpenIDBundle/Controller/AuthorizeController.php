<?php

namespace LoginCidadao\OpenIDBundle\Controller;

use FOS\OAuthServerBundle\Event\OAuthEvent;
use Symfony\Component\HttpFoundation\Request;
use OAuth2\ServerBundle\Controller\AuthorizeController as BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class AuthorizeController extends BaseController
{

    /**
     * @Route("/openid/connect/authorize", name="_authorize_handle")
     * @Method({"POST"})
     */
    public function handleAuthorizeAction()
    {
        $request       = $this->getRequest();
        $scope         = $request->request->get('scope');
        $is_authorized = $request->request->has('rejected') === false || $request->request->has('accepted')
            === true;
        $request->request->set('scope', implode(' ', $scope));

        $server = $this->get('oauth2.server');

        $response = $this->handleAuthorize($server, $is_authorized);

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
        $scopes       = array_map(function ($value) {
            return $value->getScope();
        }, $scopeManager->findScopesByScopes($scope));

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

    /**
     * @Route("/openid/connect/authorize", name="_authorize_validate")
     * @Method({"GET"})
     * @Template("OAuth2ServerBundle:Authorize:authorize.html.twig")
     */
    public function validateAuthorizeAction()
    {
        $request = $this->getRequest();
        $id      = explode('_', $request->get('client_id'));
        $em      = $this->getDoctrine()->getManager();
        $client  = $em->getRepository('PROCERGSOAuthBundle:Client')->find($id[0]);

        $event = $this->get('event_dispatcher')->dispatch(
            OAuthEvent::PRE_AUTHORIZATION_PROCESS,
            new OAuthEvent($this->getUser(), $client)
        );

        $server = $this->get('oauth2.server');
        if ($event->isAuthorizedClient()) {
            return $this->handleAuthorize($server, $event->isAuthorizedClient());
        }

        return parent::validateAuthorizeAction();
    }

    private function handleAuthorize($server, $is_authorized)
    {
        return $server->handleAuthorizeRequest($this->get('oauth2.request'),
                $this->get('oauth2.response'), $is_authorized,
                $this->getUser()->getId());
    }
}
