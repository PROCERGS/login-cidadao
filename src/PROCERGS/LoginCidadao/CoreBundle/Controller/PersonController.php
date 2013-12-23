<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

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
        
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new GetSetMethodNormalizer());

        $serializer = new Serializer($normalizers, $encoders);

        $json = $serializer->serialize($user, 'json');
        die($json);
    }

}
