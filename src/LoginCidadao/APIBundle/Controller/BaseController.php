<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\APIBundle\Controller;

use FOS\OAuthServerBundle\Security\Authentication\Token\OAuthToken;
use FOS\RestBundle\Controller\FOSRestController;
use JMS\Serializer\SerializationContext;
use LoginCidadao\APIBundle\Service\VersionService;
use LoginCidadao\CoreBundle\Entity\Authorization;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\OAuthBundle\Entity\AccessToken;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class BaseController extends FOSRestController
{
    protected function renderWithContext($content, $context = null)
    {
        $person = $this->getUser();

        if (null === $context) {
            $scope = $this->getClientScope($person);
            $context = $this->getSerializationContext($scope);
        }

        $view = $this->view($content)
            ->setSerializationContext($context);

        return $this->handleView($view);
    }

    protected function getClientScope(
        PersonInterface $user,
        ClientInterface $client = null
    ) {
        if ($client === null) {
            $client = $this->getClient();
        }

        $authorization = $this->getDoctrine()
            ->getRepository('LoginCidadaoCoreBundle:Authorization')
            ->findOneBy(['person' => $user, 'client' => $client]);
        if (!($authorization instanceof Authorization)) {
            throw new AccessDeniedException("Access denied");
        }

        $scopes = $authorization->getScope();
        if (array_search('public', $scopes) === false) {
            $scopes[] = 'public';
        }

        return $scopes;
    }

    protected function getSerializationContext($scope)
    {
        /** @var SerializationContext $context */
        $context = SerializationContext::create()->setGroups($scope);

        /** @var VersionService $versionService */
        $versionService = $this->get('lc.api.version');
        $version = $versionService->getString($versionService->getVersionFromRequest());

        $context->setVersion(/** @scrutinizer ignore-type */ $version);

        return $context;
    }

    /**
     * Gets the authenticated Client.
     *
     * @return ClientInterface
     */
    protected function getClient()
    {
        /** @var TokenStorageInterface $tokenStorage */
        $tokenStorage = $this->get('security.token_storage');

        $token = $tokenStorage->getToken();

        if (!$token instanceof OAuthToken) {
            return null;
        }

        $accessToken = $this->getDoctrine()
            ->getRepository('LoginCidadaoOAuthBundle:AccessToken')
            ->findOneBy(['token' => $token->getToken()]);

        if (!$accessToken instanceof AccessToken) {
            return null;
        }

        return $accessToken->getClient();
    }
}
