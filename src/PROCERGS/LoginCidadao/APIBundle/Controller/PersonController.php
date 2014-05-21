<?php

namespace PROCERGS\LoginCidadao\APIBundle\Controller;

use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PersonController extends Controller
{

    /**
     * @Route("/person")
     * @Template()
     */
    public function selfAction()
    {
        $kernel = $this->get('kernel');
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

        // User's profile picture
        if ($user->hasLocalProfilePicture()) {
            $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
            $picturePath = $helper->asset($user, 'image');
            $pictureUrl = $this->getRequest()->getUriForPath($picturePath);
            if ($kernel->getEnvironment() === 'dev') {
                $pictureUrl = str_replace('/app_dev.php', '', $pictureUrl);
            }
        } else {
            $pictureUrl = $user->getSocialNetworksPicture();
        }
        if (is_null($pictureUrl)) {
            // TODO: fix this and make it comply to DRY
            $picturePath = $this->get('templating.helper.assets')->getUrl('bundles/procergslogincidadaocore/images/userav.png');
            $pictureUrl = $this->getRequest()->getUriForPath($picturePath);
            if ($kernel->getEnvironment() === 'dev') {
                $pictureUrl = str_replace('/app_dev.php', '', $pictureUrl);
            }
        }
        $user->setProfilePictureUrl($pictureUrl);

        $user->serialize();
        $json = $serializer->serialize($user, 'json', SerializationContext::create()->setGroups($scope));

        $response = new JsonResponse();
        $response->setData(json_decode($json));
        return $response;
    }

    /**
     * @Route("/person/voter-registration", defaults={"_format" = "json"})
     * @Template()
     */
    public function voterRegistrationAction()
    {
        $kernel = $this->get('kernel');
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

        if (array_search('voter_registration', $scope) !== false) {
            $json = json_encode(array('voter_registration' => $user->getVoterRegistration()));
        } else {
            throw new AccessDeniedException();
        }

        $response = new JsonResponse();
        $response->setData(json_decode($json));
        return $response;
    }
}
