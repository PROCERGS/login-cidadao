<?php

namespace PROCERGS\LoginCidadao\APIBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Person;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Authorization;
use FOS\OAuthServerBundle\Model\ClientInterface;

class BaseController extends FOSRestController
{

    protected function renderWithContext($content)
    {
        $person = $this->getUser();
        $scope = $this->getClientScope($person);

        $view = $this->view($content)
                ->setSerializationContext($this->getSerializationContext($scope));
        return $this->handleView($view);
    }

    protected function serializePerson($person, $scope)
    {
        $person = $this->getUser();
        $serializer = $this->get('jms_serializer');
        return $serializer->serialize($person, 'json',
                        SerializationContext::create()->setGroups($scope));
    }

    protected function getClientScope($user)
    {
        $client = $this->getClient();

        $authorization = $this->getDoctrine()
                ->getRepository('PROCERGSLoginCidadaoCoreBundle:Authorization')
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
        $accessToken = $this->getDoctrine()->getRepository('PROCERGSOAuthBundle:AccessToken')->findOneBy(array('token' => $token->getToken()));
        $client = $accessToken->getClient();
        return $client;
    }

}
