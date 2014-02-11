<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Controller;

use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PersonController extends Controller
{

    public function connectFacebookWithAccountAction()
    {
        $fbService = $this->get('fos_facebook.user.login');
        //todo: check if service is successfully connected.
        $fbService->connectExistingAccount();
        return $this->redirect($this->generateUrl('fos_user_profile_edit'));
    }

    public function loginFbAction()
    {
        return $this->redirect($this->generateUrl("_homepage"));
    }

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

    /**
     * @Route("/connectTwitter", name="connect_twitter")
     *
     */
    public function connectTwitterAction()
    {
        $request = $this->get('request');
        $twitter = $this->get('fos_twitter.service');

        $authURL = $twitter->getLoginUrl($request);

        $response = new RedirectResponse($authURL);

        return $response;
    }

    /**
     * @Route("/person/check_username", name="ui_check_username")
     */
    public function checkUsernameAction(Request $request)
    {
        $username = $request->get('username');
        $person = $this->getDoctrine()
                ->getRepository('PROCERGSLoginCidadaoCoreBundle:Person')
                ->findByUsername($username);

        $result = count($person) > 0;

        $response = new JsonResponse();
        $response->setData(json_encode(array('success' => $result)));
        return $response;
    }

    /**
     * @Route("/person/username/update", name="lc_update_username")
     * @Template()
     */
    public function updateUsernameAction()
    {
        return array();
    }
}
