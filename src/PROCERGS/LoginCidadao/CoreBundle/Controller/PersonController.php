<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Controller;

use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PersonController extends Controller
{

    /**
     * @Route("/person")
     * @Template()
     */
    public function selfAction()
    {
        $token = $this->get('security.context')->getToken();
        $user = $token->getUser();

        $accessToken = $this->getDoctrine()->getRepository('PROCERGSOAuthBundle:AccessToken')->findOneBy(array('token' => $token->getToken()));
        $client = $accessToken->getClient();

        $authorization = $this->getDoctrine()
                ->getRepository('PROCERGSLoginCidadaoCoreBundle:Authorization')
                ->findOneBy(array(
            'person' => $user,
            'client' => $client
        ));
        $scope = $authorization->getScope();

        $serializer = $this->container->get('jms_serializer');

        $json = $serializer->serialize($user, 'json', SerializationContext::create()->setGroups($scope));

        $response = new JsonResponse();
        $response->setData(json_decode($json));
        return $response;
    }

}
