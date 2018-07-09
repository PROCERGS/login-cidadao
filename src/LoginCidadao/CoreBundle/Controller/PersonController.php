<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Controller;

use LoginCidadao\BadgesControlBundle\Handler\BadgesHandler;
use LoginCidadao\CoreBundle\Entity\Authorization;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\OAuthBundle\Entity\ClientRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Util\TokenGenerator;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Event\FormEvent;
use LoginCidadao\CoreBundle\EventListener\ProfileEditListener;
use LoginCidadao\CoreBundle\Form\Type\DocRgFormType;
use LoginCidadao\CoreBundle\Entity\IdCard;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormError;
use LoginCidadao\CoreBundle\Helper\GridHelper;
use Symfony\Component\Translation\TranslatorInterface;

class PersonController extends Controller
{
    /**
     * @Route("/person/authorization/{clientId}/revoke", name="lc_revoke")
     * @Template()
     */
    public function revokeAuthorizationAction(Request $request, $clientId)
    {
        $form = $this->createForm('LoginCidadao\CoreBundle\Form\Type\RevokeAuthorizationFormType');
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->revoke($clientId);
        } else {
            $this->addFlash('error', $this->trans("Wasn't possible to disable this service."));
        }

        $url = $this->generateUrl('lc_app_details', ['clientId' => $clientId]);

        return $this->redirect($url);
    }

    /**
     * @Route("/person/checkEmailAvailable", name="lc_email_available")
     */
    public function checkEmailAvailableAction(Request $request)
    {
        $translator = $this->get('translator');
        $email = $request->get('email');

        $person = $this->getDoctrine()
            ->getRepository('LoginCidadaoCoreBundle:Person')
            ->findBy(['email' => $email]);

        $data = ['valid' => true];
        if (count($person) > 0) {
            $data = [
                'valid' => false,
                'message' => $translator->trans('The email is already used'),
            ];
        }

        $response = new JsonResponse();
        $response->setData($data);

        return $response;
    }

    /**
     * @Route("/profile/change-username", name="lc_update_username")
     * @Security("has_role('FEATURE_EDIT_USERNAME')")
     * @Template()
     */
    public function updateUsernameAction(Request $request)
    {
        $user = $this->getUser();
        $userManager = $this->get('fos_user.user_manager');

        $formBuilder = $this->createFormBuilder($user)
            ->add('username', 'Symfony\Component\Form\Extension\Core\Type\TextType')
            ->add('save', 'Symfony\Component\Form\Extension\Core\Type\SubmitType');

        $emptyPassword = strlen($user->getPassword()) == 0;
        if ($emptyPassword) {
            $formBuilder->add('plainPassword',
                'Symfony\Component\Form\Extension\Core\Type\RepeatedType',
                ['type' => 'password']);
        } else {
            $formBuilder->add('current_password',
                'Symfony\Component\Form\Extension\Core\Type\PasswordType',
                [
                    'required' => true,
                    'constraints' => new UserPassword(),
                    'mapped' => false,
                ]);
        }

        $form = $formBuilder->getForm();

        $form->handleRequest($request);
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
                $dispatcher = $this->get('event_dispatcher');
                $dispatcher->dispatch(FOSUserEvents::CHANGE_PASSWORD_COMPLETED,
                    new FilterUserResponseEvent($user, $request, $response));
            }

            return $response;
        }

        return ['form' => $form->createView(), 'emptyPassword' => $emptyPassword];
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
    public function resendConfirmationEmailAction()
    {
        $mailer = $this->get('fos_user.mailer');
        $translator = $this->get('translator');
        $person = $this->getUser();

        if (is_null($person->getEmailConfirmedAt())) {
            if (is_null($person->getConfirmationToken())) {
                $tokenGenerator = new TokenGenerator();
                $person->setConfirmationToken($tokenGenerator->generateToken());
                $userManager = $this->get('fos_user.user_manager');
                $userManager->updateUser($person);
            }
            $mailer->sendConfirmationEmailMessage($person);
            $this->get('session')->getFlashBag()->add('success',
                $translator->trans("email-confirmation.resent"));
        }

        return $this->redirect($this->generateUrl('fos_user_profile_edit'));
    }

    /**
     * @Route("/profile/badges", name="lc_profile_badges")
     * @Template()
     */
    public function badgesListAction(Request $request)
    {
        /** @var BadgesHandler $badgesHandler */
        $badgesHandler = $this->get('badges.handler');

        $badges = $badgesHandler->getAvailableBadges();
        $user = $badgesHandler->evaluate($this->getUser());

        return ['allBadges' => $badges, 'userBadges' => $user->getBadges()];
    }

    private function removeAll(array $objects)
    {
        $em = $this->getDoctrine()->getManager();
        foreach ($objects as $object) {
            $em->remove($object);
        }
    }

    private function trans($id, array $parameters = [], $domain = null, $locale = null)
    {
        /** @var TranslatorInterface $translator */
        $translator = $this->get('translator');

        return $translator->trans($id, $parameters, $domain, $locale);
    }

    private function getTokens($clientId)
    {
        $user = $this->getUser();
        $client = $this->getClient($clientId);
        $em = $this->getDoctrine()->getManager();
        $accessTokens = $em->getRepository('LoginCidadaoOAuthBundle:AccessToken')->findBy([
            'client' => $client,
            'user' => $user,
        ]);
        $refreshTokens = $em->getRepository('LoginCidadaoOAuthBundle:RefreshToken')->findBy([
            'client' => $client,
            'user' => $user,
        ]);


        return array_merge($accessTokens, $refreshTokens);
    }

    private function getClient($clientId)
    {
        return $this->getDoctrine()->getManager()->getRepository('LoginCidadaoOAuthBundle:Client')->find($clientId);
    }

    private function getAuthorization($clientId)
    {
        $auth = $this->getDoctrine()->getRepository('LoginCidadaoCoreBundle:Authorization')
            ->findOneBy([
                'person' => $this->getUser(),
                'client' => $this->getClient($clientId),
            ]);

        if (!$auth) {
            throw new \InvalidArgumentException($this->trans("Authorization not found."));
        }

        return $auth;
    }

    private function revoke($clientId)
    {
        try {
            if (false === $this->isGranted('ROLE_USER')) {
                throw new AccessDeniedException();
            }

            $this->removeAll(array_merge($this->getTokens($clientId), [$this->getAuthorization($clientId)]));
            $this->addFlash('success', $this->trans('Authorization successfully revoked.'));

            $this->getDoctrine()->getManager()->flush();
        } catch (AccessDeniedException $e) {
            $this->addFlash('error', $this->trans("Access Denied."));
        } catch (\Exception $e) {
            $this->addFlash('error', $this->trans("Wasn't possible to disable this service."));
            $this->addFlash('error', $e->getMessage());
        }
    }
}
