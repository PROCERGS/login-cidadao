<?php

namespace LoginCidadao\APIBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use LoginCidadao\CoreBundle\Entity\Person;
use LoginCidadao\CoreBundle\Entity\Authorization;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use LoginCidadao\OAuthBundle\Model\ClientUser;

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

    protected function getClientScope(PersonInterface $user,
                                      ClientInterface $client = null)
    {
        if ($client === null) {
            $client = $this->getClient();
        }

        $authorization = $this->getDoctrine()
            ->getRepository('LoginCidadaoCoreBundle:Authorization')
            ->findOneBy(array(
            'person' => $user,
            'client' => $client
        ));
        if (!($authorization instanceof Authorization)) {
            throw new AccessDeniedHttpException("Access denied");
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
        $token = $this->get('security.context')->getToken();
        $accessToken = $this->getDoctrine()->
            getRepository('LoginCidadaoOAuthBundle:AccessToken')->
            findOneBy(array('token' => $token->getToken()));
        $client = $accessToken->getClient();
        return $client;
    }

}
