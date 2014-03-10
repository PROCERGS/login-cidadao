<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use PROCERGS\LoginCidadao\CoreBundle\Helper\NfgHelper;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FilterUserResponseEvent;

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
     * @Route("/person/checkEmailAvailable", name="lc_email_available")
     */
    public function checkEmailAvailableAction(Request $request)
    {
        $translator = $this->get('translator');
        $email = $request->get('email');

        $person = $this->getDoctrine()
                ->getRepository('PROCERGSLoginCidadaoCoreBundle:Person')
                ->findByEmail($email);

        $data = array('valid'=>true);
        if(count($person) > 0){
            $data = array(
                'valid' => false,
                'message' => $translator->trans('The email is already used')
            );
        }

        $response = new JsonResponse();
        $response->setData($data);

        return $response;
    }

    /**
     * @Route("/profile/change-username", name="lc_update_username")
     * @Template()
     */
    public function updateUsernameAction()
    {
        $user = $this->getUser();
        $userManager = $this->container->get('fos_user.user_manager');

        $formBuilder = $this->createFormBuilder($user)
            ->add('username', 'text')
            ->add('save', 'submit');

        $emptyPassword = strlen($user->getPassword()) == 0;
        if ($emptyPassword) {
            $formBuilder->add('plainPassword', 'repeated', array(
                'type' => 'password'
            ));
        } else {
            $formBuilder->add('current_password', 'password', array(
                'required' => true,
                'constraints' => new UserPassword(),
                'mapped' => false
            ));
        }

        $form = $formBuilder->getForm();

        $form->handleRequest($this->getRequest());
        if ($form->isValid()) {
            $data = $form->getData();
            $user->setUsername($data->getUsername());

            $userManager->updateUser($user);

            $translator = $this->get('translator');
            $this->get('session')->getFlashBag()->add('notice',$translator->trans('Updated username successfully!'));

            $response = $this->redirect($this->generateUrl('lc_update_username'));

            $request = $this->getRequest();
            $dispatcher = $this->container->get('event_dispatcher');
            $dispatcher->dispatch(FOSUserEvents::CHANGE_PASSWORD_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

            return $response;
        }

        return array('form' => $form->createView(), 'emptyPassword' => $emptyPassword);
    }

    /**
     * @Route("/registration/cpf", name="lc_registration_cpf")
     * @Template()
     */
    public function registrationCpfAction(Request $request)
    {
        $person = $this->getUser();
        $form = $this->createForm('cpf_form_type', $person);
        $form->handleRequest($this->getRequest());
        if ($form->isValid()) {
            $person->setCpfExpiration(null);
            if ($form->get('nfgPassword')->getData()) {
                $nfg = new NfgHelper();
                $nfg->setUsername($person->getCpf());
                $nfg->setPassword($form->get('nfgPassword')->getData());
                if ($profile = $nfg->profile()) {
                    $person->setCpfNfg(new \DateTime());
                } else {
                    $person->setCpfNfg(null);
                }
            }
            $this->container->get('fos_user.user_manager')->updateUser($person);
            return $this->redirect($this->generateUrl('lc_home'));
        }
        return array(
            'form' => $form->createView()
        );
    }
}
