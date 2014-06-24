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
use FOS\UserBundle\Util\TokenGenerator;
use PROCERGS\LoginCidadao\CoreBundle\Form\Type\DocFormType;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Event\FormEvent;
use PROCERGS\LoginCidadao\CoreBundle\EventListener\ProfileEditListner;

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
     * @Route("/person/authorization/{clientId}/revoke", name="lc_revoke")
     * @Template()
     */
    public function revokeAuthorizationAction($clientId)
    {
        $form = $this->createForm('procergs_revoke_authorization');
        $form->handleRequest($this->getRequest());

        if ($form->isValid()) {
            $security = $this->get('security.context');
            $em = $this->getDoctrine()->getManager();
            $tokens = $em->getRepository('PROCERGSOAuthBundle:AccessToken');
            $clients = $em->getRepository('PROCERGSOAuthBundle:Client');
            $translator = $this->get('translator');

            try {

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
                $success = false;

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

                        $this->get('session')->getFlashBag()->add('success',
                                $translator->trans('Authorization successfully revoked.'));
                        $success = true;
                    }
                }

                if (!$success) {
                    throw new \InvalidArgumentException($translator->trans("Authorization not found."));
                }
            } catch (AccessDeniedException $e) {
                $this->get('session')->getFlashBag()->add('error',
                        $translator->trans("Access Denied."));
            } catch (\Exception $e) {
                $this->get('session')->getFlashBag()->add('error',
                        $translator->trans("Wasn't possible to disable this service."));
                $this->get('session')->getFlashBag()->add('error',
                        $e->getMessage());
            }
        } else {
            $this->get('session')->getFlashBag()->add('error',
                    $translator->trans("Wasn't possible to disable this service."));
        }

        return $this->redirect($this->generateUrl('lc_app_details',
                                array('clientId' => $clientId)));
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

        $data = array('valid' => true);
        if (count($person) > 0) {
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
            $formBuilder->add('plainPassword', 'repeated',
                    array(
                'type' => 'password'
            ));
        } else {
            $formBuilder->add('current_password', 'password',
                    array(
                'required' => true,
                'constraints' => new UserPassword(),
                'mapped' => false
            ));
        }

        $form = $formBuilder->getForm();

        $form->handleRequest($this->getRequest());
        if ($form->isValid()) {
            $data = $form->getData();
            $hasChangedPassword = $data->getPassword() == '';
            $user->setUsername($data->getUsername());

            $userManager->updateUser($user);

            $translator = $this->get('translator');
            $this->get('session')->getFlashBag()->add('success',
                    $translator->trans('Updated username successfully!'));

            $response = $this->redirect($this->generateUrl('lc_update_username'));
            if ($hasChangedPassword) {
                $request = $this->getRequest();
                $dispatcher = $this->container->get('event_dispatcher');
                $dispatcher->dispatch(FOSUserEvents::CHANGE_PASSWORD_COMPLETED,
                        new FilterUserResponseEvent($user, $request, $response));
            }
            return $response;
        }

        return array('form' => $form->createView(), 'emptyPassword' => $emptyPassword);
    }

    /**
     * @Route("/cpf/register", name="lc_registration_cpf")
     * @Template("PROCERGSLoginCidadaoCoreBundle:Person:registration/cpf.html.twig")
     */
    public function registrationCpfAction(Request $request)
    {
        $person = $this->getUser();
        if (is_numeric($cpf = preg_replace('/[^0-9]/', '', $request->get('cpf'))) && strlen($cpf) == 11) {
            $person->setCpf($cpf);
        }
        $formBuilder = $this->createFormBuilder($person);
        if (!$person->getCpf()) {
            $formBuilder->add('cpf', 'text', array('required' => true));
        }
        $form = $formBuilder->getForm();
        $form->handleRequest($this->getRequest());
        $messages = '';
        if ($form->isValid()) {
            $person->setCpfExpiration(null);
            $this->container->get('fos_user.user_manager')->updateUser($person);
            return $this->redirect($this->generateUrl('lc_home'));
        }
        return array(
            'form' => $form->createView(), 'messages' => $messages, 'isExpired' => $person->isCpfExpired()
        );
    }

    /**
     * @Route("/facebook/unlink", name="lc_unlink_facebook")
     */
    public function unlinkFacebookAction()
    {
        $person = $this->getUser();
        $translator = $this->get('translator');
        if ($person->hasPassword()) {
            $person->setFacebookId(null)
                    ->setFacebookUsername(null);
            $userManager = $this->get('fos_user.user_manager');
            $userManager->updateUser($person);

            $this->get('session')->getFlashBag()->add('success',
                    $translator->trans("social-networks.unlink.facebook.success"));
        } else {
            $this->get('session')->getFlashBag()->add('error',
                    $translator->trans("social-networks.unlink.no-password"));
        }

        return $this->redirect($this->generateUrl('fos_user_profile_edit'));
    }

    /**
     * @Route("/twitter/unlink", name="lc_unlink_twitter")
     */
    public function unlinkTwitterAction()
    {
        $person = $this->getUser();
        $translator = $this->get('translator');
        if ($person->hasPassword()) {
            $person->setTwitterId(null)
                    ->setTwitterUsername(null)
                    ->setTwitterAccessToken(null);
            $userManager = $this->get('fos_user.user_manager');
            $userManager->updateUser($person);

            $this->get('session')->getFlashBag()->add('success',
                    $translator->trans("social-networks.unlink.twitter.success"));
        } else {
            $this->get('session')->getFlashBag()->add('error',
                    $translator->trans("social-networks.unlink.no-password"));
        }

        return $this->redirect($this->generateUrl('fos_user_profile_edit'));
    }
    
    /**
     * @Route("/google/unlink", name="lc_unlink_google")
     */
    public function unlinkGoogleAction()
    {
        $person = $this->getUser();
        $translator = $this->get('translator');
        if ($person->hasPassword()) {
            $person->setGoogleId(null)
            ->setGoogleUsername(null)
            ->setGoogleAccessToken(null);
            $userManager = $this->get('fos_user.user_manager');
            $userManager->updateUser($person);
    
            $this->get('session')->getFlashBag()->add('success',
                $translator->trans("social-networks.unlink.google.success"));
        } else {
            $this->get('session')->getFlashBag()->add('error',
                $translator->trans("social-networks.unlink.no-password"));
        }
    
        return $this->redirect($this->generateUrl('fos_user_profile_edit'));
    }

    /**
     * @Route("/email/resend-confirmation", name="lc_resend_confirmation_email")
     */
    public function resendConfirmationEmail()
    {
        $mailer = $this->get('fos_user.mailer');
        $translator = $this->get('translator');
        $person = $this->getUser();

        if (is_null($person->getEmailConfirmedAt())) {
            if(is_null($person->getConfirmationToken())) {
                $tokenGenerator = new TokenGenerator();
                $person->setConfirmationToken($tokenGenerator->generateToken());
                $userManager = $this->get('fos_user.user_manager');
                $userManager->updateUser($person);
            }
            $mailer->sendConfirmationEmailMessage($person);
            $this->get('session')->getFlashBag()->add('success', $translator->trans("email-confirmation.resent"));
        }

        return $this->redirect($this->generateUrl('fos_user_profile_edit'));
    }
    
    /**
     * @Route("/profile/doc/edit", name="lc_profile_doc_edit")
     * @Template()
     */
    public function docEditAction(Request $request)
    {
        $user = $this->getUser();
        
        $dispatcher = $this->container->get('event_dispatcher');
        
        $event = new GetResponseUserEvent($user, $request);        
        $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_INITIALIZE, $event);
        
        $form = $this->createForm(new DocFormType(), $user);
        $form->handleRequest($this->getRequest());
        if ($form->isValid()) {
            
            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(ProfileEditListner::PROFILE_DOC_EDIT_SUCCESS, $event);
            
            $userManager = $this->get('fos_user.user_manager');
            $userManager->updateUser($user);
            $translator = $this->get('translator');
            $this->get('session')->getFlashBag()->add('success', $translator->trans("Documents were successfully changed"));
        }
        return array('form' => $form->createView());
    }

}
