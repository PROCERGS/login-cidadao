<?php

namespace LoginCidadao\OpenIDBundle\Controller;

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
    public function handleAuthorizeAction()
    {
        $server = $this->get('oauth2.server');

        return $server->handleAuthorizeRequest($this->get('oauth2.request'),
                $this->get('oauth2.response'), true, $this->getUser()->getId());
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


        $scopes       = array();
        $scopeStorage = $this->get('oauth2.storage.scope');
        foreach (explode(' ', $scope) as $scope) {
            $scopes[] = $scopeStorage->getDescriptionForScope($scope);
        }

        $qs = compact('client_id', 'scope', 'response_type', 'redirect_uri',
            'state', 'nonce');
        return compact('qs', 'scopes', 'client');
    }
}
