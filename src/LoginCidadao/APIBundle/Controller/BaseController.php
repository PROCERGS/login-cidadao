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

use FOS\RestBundle\Controller\FOSRestController;
use JMS\Serializer\SerializationContext;
use LoginCidadao\CoreBundle\Entity\Authorization;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
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

    protected function serializePerson($person, $scope)
    {
        $person = $this->getUser();
        $serializer = $this->get('jms_serializer');

        return $serializer->serialize($person, 'json',
            SerializationContext::create()->setGroups($scope));
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
            ->findOneBy(array(
                'person' => $user,
                'client' => $client,
            ));
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
        return SerializationContext::create()->setGroups($scope);
    }

    /**
     * Gets the authenticated Client.
     *
     * @return ClientInterface
     */
    protected function getClient()
    {
        $token = $this->get('security.token_storage')->getToken();
        $accessToken = $this->getDoctrine()->
        getRepository('LoginCidadaoOAuthBundle:AccessToken')->
        findOneBy(array('token' => $token->getToken()));
        $client = $accessToken->getClient();

        return $client;
    }

}
