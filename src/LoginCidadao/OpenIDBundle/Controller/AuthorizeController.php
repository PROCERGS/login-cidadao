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
use LoginCidadao\OAuthBundle\Entity\Organization;
use LoginCidadao\OpenIDBundle\Entity\ClientMetadata;
use LoginCidadao\OpenIDBundle\Manager\ClientManager;
use LoginCidadao\OpenIDBundle\Validator\SectorIdentifierUriChecker;
use Symfony\Component\HttpFoundation\Request;
use OAuth2\ServerBundle\Controller\AuthorizeController as BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use LoginCidadao\OAuthBundle\Model\OrganizationInterface;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthorizeController extends BaseController
{

    /**
     * @Route("/openid/connect/authorize", name="_authorize_handle")
     * @Method({"POST"})
     */
    public function handleAuthorizeAction()
    {
        $request = $this->getRequest();
        $scope = $request->request->get('scope');
        $is_authorized = $request->request->has('rejected') === false || $request->request->has('accepted')
            === true;
        $request->request->set('scope', implode(' ', $scope));

        $server = $this->get('oauth2.server');
        $client = $this->getClient($request);

        $response = $this->handleAuthorize($server, $is_authorized);

        $event = new OAuthEvent($this->getUser(), $client, $is_authorized);
        $this->get('event_dispatcher')
            ->dispatch(OAuthEvent::POST_AUTHORIZATION_PROCESS, $event);

        return $response;
    }

    /**
     * @Template()
     */
    public function authorizeAction(
        $client_id,
        $scope,
        $response_type,
        $redirect_uri,
        $state = null,
        $nonce = null
    ) {
        $client = $this->getClient($client_id);

        $scope = explode(' ', $scope);
        if (array_search('public_profile', $scope) === false) {
            $scope[] = 'public_profile';
        }

        $scopeManager = $this->getScopeManager();
        $scopes = array_map(
            function ($value) {
                return $value->getScope();
            },
            $scopeManager->findScopesByScopes($scope)
        );

        $warnUntrusted = $this->shouldWarnUntrusted($client);
        $metadata = $this->getMetadata($client);
        $organization = $this->getOrganization($metadata);

        $qs = compact(
            'client_id',
            'scope',
            'response_type',
            'redirect_uri',
            'state',
            'nonce'
        );

        return compact('qs', 'scopes', 'client', 'warnUntrusted', 'metadata', 'organization');
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

        if ($client instanceof \FOS\OAuthServerBundle\Model\ClientInterface) {
            $event = $this->get('event_dispatcher')->dispatch(
                OAuthEvent::PRE_AUTHORIZATION_PROCESS,
                new OAuthEvent($this->getUser(), $client)
            );

            $shouldPrompt = $request->get('prompt', null) == 'consent';

            $server = $this->get('oauth2.server');
            if ($event->isAuthorizedClient() && !$shouldPrompt) {
                return $this->handleAuthorize(
                    $server,
                    $event->isAuthorizedClient()
                );
            }
        }

        return parent::validateAuthorizeAction();
    }

    /**
     * @return \OAuth2\ServerBundle\Manager\ScopeManager
     */
    private function getScopeManager()
    {
        return $this->get('oauth2.scope_manager');
    }

    private function handleAuthorize($server, $is_authorized)
    {
        return $server->handleAuthorizeRequest(
            $this->get('oauth2.request'),
            $this->get('oauth2.response'),
            $is_authorized,
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

    private function getOrganization(ClientMetadata $metadata = null)
    {
        if ($metadata === null) {
            return null;
        }

        if ($metadata->getOrganization() === null && $metadata->getSectorIdentifierUri()) {
            $sectorIdentifierUri = $metadata->getSectorIdentifierUri();
            try {
                $verified = $this->getSectorIdentifierUriChecker()->check($metadata, $sectorIdentifierUri);
            } catch (HttpException $e) {
                $verified = false;
            }
            $uri = parse_url($sectorIdentifierUri);
            $domain = $uri['host'];

            $organization = new Organization();
            $organization->setDomain($domain)
                ->setName($domain)
                ->setTrusted(false)
                ->setVerifiedAt($verified ? new \DateTime() : null);

            return $organization;
        }

        return $metadata->getOrganization();
    }

    /**
     * @return SectorIdentifierUriChecker
     */
    private function getSectorIdentifierUriChecker()
    {
        return $this->get('checker.sector_identifier_uri');
    }
}
