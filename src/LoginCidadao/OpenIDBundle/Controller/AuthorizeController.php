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

use FOS\OAuthServerBundle\Event\OAuthEvent;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\OpenIDBundle\Event\AuthorizationEvent;
use LoginCidadao\OpenIDBundle\LoginCidadaoOpenIDEvents;
use LoginCidadao\OpenIDBundle\Manager\ClientManager;
use LoginCidadao\OpenIDBundle\Manager\ScopeManager;
use LoginCidadao\OAuthBundle\Service\OrganizationService;
use LoginCidadao\OAuthBundle\Model\OrganizationInterface;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use LoginCidadao\OpenIDBundle\Validator\SectorIdentifierUriChecker;
use OAuth2\Server;
use OAuth2\ServerBundle\Entity\Scope;
use OAuth2\ServerBundle\Controller\AuthorizeController as BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AuthorizeController extends BaseController
{

    /**
     * @Route("/openid/connect/authorize", name="_authorize_handle")
     * @Method({"POST"})
     */
    public function handleAuthorizeAction()
    {
        $request = $this->getRequest();
        $implodedScope = implode(' ', $request->request->get('scope'));
        $request->request->set('scope', $implodedScope);

        $isAuthorized = $request->request->has('accepted')
            || !$request->request->has('rejected');

        $response = $this->handleAuthorize($this->getOAuth2Server(), $isAuthorized);

        $event = new OAuthEvent(
            $this->getUser(),
            $this->getClient($request), $isAuthorized
        );
        $this->get('event_dispatcher')->dispatch(OAuthEvent::POST_AUTHORIZATION_PROCESS, $event);

        return $response;
    }

    /**
     * Render the Authorization fragment
     *
     * @Template()
     * @param Request $request
     * @return array
     */
    public function authorizeAction(Request $request)
    {
        $client = $this->getClient($request);

        $scope = explode(' ', $request->get('scope'));
        if (array_search('public_profile', $scope) === false) {
            $scope[] = 'public_profile';
        }

        /** @var ScopeManager $scopeManager */
        $scopeManager = $this->get('oauth2.scope_manager');
        $scopes = array_map(
            function (Scope $value) {
                return $value->getScope();
            },
            $scopeManager->findScopesByScopes($scope)
        );

        /** @var OrganizationService $organizationService */
        $organizationService = $this->get('organization');
        $warnUntrusted = $this->shouldWarnUntrusted($client);
        $metadata = $this->getMetadata($client);
        $organization = $organizationService->getOrganization($metadata);

        $qs = [
            'client_id' => $client->getPublicId(),
            'scope' => $scope,
            'response_type' => $request->get('response_type'),
            'redirect_uri' => $request->get('redirect_uri'),
            'state' => $request->get('state'),
            'nonce' => $request->get('nonce'),
        ];

        return [
            'qs' => $qs,
            'scopes' => $scopes,
            'client' => $client,
            'warnUntrusted' => $warnUntrusted,
            'metadata' => $metadata,
            'organization' => $organization,
        ];
    }

    /**
     * @Route("/openid/connect/authorize", name="_authorize_validate")
     * @Method({"GET"})
     * @Template("OAuth2ServerBundle:Authorize:authorize.html.twig")
     */
    public function validateAuthorizeAction()
    {
        $request = $this->getRequest();
        $client = $this->getClient($request);

        if (!$client instanceof \FOS\OAuthServerBundle\Model\ClientInterface) {
            return parent::validateAuthorizeAction();
        }

        /** @var PersonInterface $person */
        $person = $this->getUser();

        /** @var EventDispatcher $dispatcher */
        $dispatcher = $this->get('event_dispatcher');

        $event = new OAuthEvent($person, $client);
        $dispatcher->dispatch(OAuthEvent::PRE_AUTHORIZATION_PROCESS, $event);

        $isAuthorized = $event->isAuthorizedClient();
        $askConsent = $request->get('prompt', null) == 'consent';

        if ($isAuthorized && !$askConsent) {
            return $this->handleAuthorize($this->getOAuth2Server(), $isAuthorized);
        }

        if (!$isAuthorized) {
            $authEvent = new AuthorizationEvent($person, $client, $request->get('scope'));
            $dispatcher->dispatch(LoginCidadaoOpenIDEvents::NEW_AUTHORIZATION_REQUEST, $authEvent);
            //dump($authEvent->getRemoteClaims());
        }

        // TODO: this following method is breaking the Remote Claim feature
        // This method checks if the requested claims are valid, but remote claims would never be considered valid
        // Alternatives:
        //      a) try to remove the remote claim from the request's scope;
        //      b) inject it to the Storage\ClientCredentials::getClientScope()
        $response = parent::validateAuthorizeAction();
        return JsonResponse::create();
        return $response;
    }

    private function handleAuthorize(Server $server, $isAuthorized)
    {
        /** @var \OAuth2\Request $request */
        $request = $this->get('oauth2.request');

        /** @var \OAuth2\Response $response */
        $response = $this->get('oauth2.response');

        return $server->handleAuthorizeRequest(
            $request,
            $response,
            $isAuthorized,
            $this->getUser()->getId()
        );
    }

    private function getClient($fullId)
    {
        if ($fullId instanceof Request) {
            $fullId = $fullId->get('client_id');
        }

        /** @var ClientManager $clientManager */
        $clientManager = $this->get('lc.client_manager');

        return $clientManager->getClientById($fullId);
    }

    private function shouldWarnUntrusted(ClientInterface $client = null)
    {
        $warnUntrusted = $this->getParameter('warn_untrusted');

        if ($client) {
            $metadata = $this->getMetadata($client);

            if ($metadata && $metadata->getOrganization() instanceof OrganizationInterface) {
                $isTrusted = $metadata->getOrganization()->isTrusted();
            } else {
                $isTrusted = false;
            }
        } else {
            $isTrusted = false;
        }

        if ($isTrusted || !$warnUntrusted) {
            return false; // do not warn
        }

        return true; // warn
    }

    private function getMetadata(ClientInterface $client = null)
    {
        if (!$client) {
            return null;
        }

        $repo = $this->getDoctrine()->getRepository('LoginCidadaoOpenIDBundle:ClientMetadata');

        return $repo->findOneBy(['client' => $client]);
    }

    /**
     * @return SectorIdentifierUriChecker
     */
    private function getSectorIdentifierUriChecker()
    {
        return $this->get('checker.sector_identifier_uri');
    }

    /**
     * @return object|Server
     */
    private function getOAuth2Server()
    {
        return $this->get('oauth2.server');
    }
}
