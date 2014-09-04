<?php

namespace PROCERGS\LoginCidadao\APIBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Person;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Authorization;

class BaseController extends FOSRestController
{

    protected function renderWithContext($content)
    {
        $person = $this->preparePerson($this->getUser());
        $scope = $this->getClientScope($person);

        $view = $this->view($content)
                ->setSerializationContext($this->getSerializationContext($scope));
        return $this->handleView($view);
    }


    protected function preparePerson(Person $person)
    {
        $imgHelper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $templateHelper = $this->get('templating.helper.assets');
        $isDev = $this->get('kernel')->getEnvironment() === 'dev';
        $person->prepareAPISerialize($imgHelper, $templateHelper, $isDev,
                $this->getRequest());

        return $person;
    }

    protected function serializePerson($person, $scope)
    {
        $person = $this->preparePerson($person, $scope);
        die(print_r($scope));
        $serializer = $this->container->get('jms_serializer');
        return $serializer->serialize($person, 'json',
                        SerializationContext::create()->setGroups($scope));
    }

    protected function getClientScope($user)
    {
        $token = $this->get('security.context')->getToken();
        $accessToken = $this->getDoctrine()->getRepository('PROCERGSOAuthBundle:AccessToken')->findOneBy(array('token' => $token->getToken()));
        $client = $accessToken->getClient();

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

}
