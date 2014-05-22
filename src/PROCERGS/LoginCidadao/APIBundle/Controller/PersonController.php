<?php

namespace PROCERGS\LoginCidadao\APIBundle\Controller;

use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use PROCERGS\LoginCidadao\APIBundle\Exception\RequestTimeoutException;

class PersonController extends Controller
{

    /**
     * @Route("/person")
     * @Template()
     */
    public function selfAction()
    {
        $user = $this->getUser();
        $scope = $this->getClientScope($user);

        $json = $this->serializePerson($user, $scope);

        $response = new JsonResponse();
        $response->setData(json_decode($json));
        return $response;
    }

    /**
     * @Route("/wait/person/voter-registration", defaults={"_format" = "json"})
     * @Template()
     */
    public function waitVoterRegistrationAction()
    {
        $user = $this->getUser();
        $scope = $this->getClientScope($user);

        if (array_search('voter_registration', $scope) !== false) {
            $people = $this->getDoctrine()->getManager()->getRepository('PROCERGSLoginCidadaoCoreBundle:Person');
            $id = $user->getId();

            $callback = function() use ($id, $people) {
                $person = $people->find($id);
                $voterRegistration = $person->getVoterRegistration();
                return (strlen($voterRegistration) > 0) ? $person : false;
            };
            $person = $this->runTimeLimited($callback);
            $json = $this->serializePerson($person, $scope);
        } else {
            throw new AccessDeniedException();
        }

        $response = new JsonResponse();
        return $response->setData(json_decode($json));
    }

    private function runTimeLimited($callback, $waitTime = 1)
    {
        $limit = ini_get('max_execution_time') - 2;
        $startTime = time();
        while ($limit > 0) {
            $result = call_user_func($callback);
            $delta = time() - $startTime;

            if ($result !== false) {
                return $result;
            } else {
                $limit -= $delta;
                $startTime = time();
                sleep($waitTime);
            }
        }
        throw new RequestTimeoutException();
    }

    private function serializePerson($person, $scope)
    {
        $imgHelper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $templateHelper = $this->get('templating.helper.assets');
        $isDev = $this->get('kernel')->getEnvironment() === 'dev';
        $person->prepareAPISerialize($imgHelper, $templateHelper, $isDev, $this->getRequest());

        $serializer = $this->container->get('jms_serializer');
        return $serializer->serialize($person, 'json',
                        SerializationContext::create()->setGroups($scope));
    }

    private function getClientScope($user)
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
        return $authorization->getScope();
    }

}
