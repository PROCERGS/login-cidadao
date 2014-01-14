<?php

namespace PROCERGS\LoginCidadao\UIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use PROCERGS\OAuthBundle\Entity\AccessToken;

class PersonController extends Controller
{

    /**
     * @Route("/person/authorization/{clientId}/revoke", name="ui_revoke")
     * @Template()
     */
    public function revokeAuthorizationAction($clientId)
    {
        $csrf = $this->get('form.csrf_provider');
        $currentUrl = $this->getRequest()->getRequestUri();
        $genToken = $csrf->generateCsrfToken($currentUrl);
        $token = $this->getRequest()->get('token');

        $response = new JsonResponse();
        $security = $this->get('security.context');
        $em = $this->getDoctrine()->getManager();
        $tokens = $em->getRepository('PROCERGSOAuthBundle:AccessToken');
        $clients = $em->getRepository('PROCERGSOAuthBundle:Client');
        $translator = $this->get('translator');

        try {
            if ($genToken !== $token) {
                throw new AccessDeniedException("CSRF detected!");
            }

            if (false === $security->isGranted('ROLE_USER')) {
                throw new AccessDeniedException();
            }

            $user = $security->getToken()->getUser();

            $client = $clients->find($clientId);
            $accessTokens = $tokens->findBy(array(
                'client' => $client,
                'user' => $user
            ));
            $refreshTokens = $em->getRepository('PROCERGSOAuthBundle:RefreshToken')
                    ->findBy(array(
                        'client' => $client,
                        'user' => $user
                    ));
            $authorizations = $user->getAuthorizations();
            foreach ($authorizations as $auth) {
                if ($auth->getPerson()->getId() == $user->getId() && $auth->getClient()->getId() == $clientId) {
                    
                    foreach ($accessTokens as $accessToken) {
                        $em->remove($accessToken);
                    }
                    
                    foreach ($refreshTokens as $refreshToken) {
                        $em->remove($refreshToken);
                    }
                    
                    $em->remove($auth);
                    $em->flush();
                    $response->setData(array(
                        'message' => $translator->trans("Authorization successfully revoked."),
                        'success' => true
                    ));

                    return $response;
                }
            }

            throw new \InvalidArgumentException($translator->trans("Authorization not found."));
        } catch (AccessDeniedException $e) {
            $response->setData(array(
                'message' => $e->getMessage(),
                'success' => false
            ));
            $response->setStatusCode(403);
            return $response;
        } catch (\Exception $e) {
            $response->setData(array(
                'message' => $e->getMessage(),
                'success' => false
            ));
            $response->setStatusCode(500);
            return $response;
        }
    }

}
