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
     * @Route("/profile/doc/edit", name="lc_profile_doc_edit")
     * @Template()
     */
    public function docEditAction(Request $request)
    {
        $user = $this->getUser();
        $dispatcher = $this->get('event_dispatcher');

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_INITIALIZE, $event);

        $form = $this->createForm('LoginCidadao\CoreBundle\Form\Type\DocFormType',
            $user);
        $form->handleRequest($request);
        if ($form->isValid()) {

            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(ProfileEditListener::PROFILE_DOC_EDIT_SUCCESS,
                $event);

            $userManager = $this->get('fos_user.user_manager');
            $userManager->updateUser($user);
            $translator = $this->get('translator');
            $this->get('session')->getFlashBag()->add('success',
                $translator->trans("Documents were successfully changed"));
        }
        $return = $this->docRgListAction($request);
        $return['form'] = $form->createView();

        return $return;
    }

    /**
     * @Route("/profile/doc/rg/remove", name="lc_profile_doc_rg_remove")
     * @Template()
     */
    public function docRgRemoveAction(Request $request)
    {
        if ($id = $request->get('id')) {
            $em = $this->getDoctrine()->getManager();
            $rg = $em->getRepository('LoginCidadaoCoreBundle:IdCard')
                ->createQueryBuilder('u')
                ->where('u.person = :person and u.id = :id')
                ->setParameter('person', $this->getUser())
                ->setParameter('id', $id)
                ->getQuery()
                ->getOneOrNullResult();
            if ($rg) {
                $em->remove($rg);
                $em->flush();
            }
        }
        $resp = new Response('<script>rgGrid.getGrid();</script>');

        return $resp;
    }

    /**
     * @Route("/profile/doc/rg/edit", name="lc_profile_doc_rg_edit")
     * @Template()
     */
    public function docRgEditAction(Request $request)
    {
        $form = $this->createForm(new DocRgFormType());
        $rg = null;
        if (($id = $request->get('id')) || (($data = $request->get($form->getName()))
                && ($id = $data['id']))) {
            $rg = $this->getDoctrine()
                ->getManager()
                ->getRepository('LoginCidadaoCoreBundle:IdCard')->findOneBy(array(
                    'person' => $this->getUser(),
                    'id' => $id,
                ));
        }
        if (!$rg) {
            $rg = new IdCard();
            $rg->setPerson($this->getUser());
        }
        $form = $this->createForm(new DocRgFormType(), $rg);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $rgNum = str_split($form->get('value')->getData());
            if (($form->get('state')->getData()->getId() == 43) && ($this->checkRGDce($rgNum)
                    != $rgNum[0] || $this->checkRGDcd($rgNum) != $rgNum[9])) {
                $form->get('value')->addError(new FormError($this->get('translator')->trans('This RG is invalid')));

                return array('form' => $form->createView());
            }

            $manager = $this->getDoctrine()->getManager();
            $dql = $manager->getRepository('LoginCidadaoCoreBundle:IdCard')
                ->createQueryBuilder('u')
                ->where('u.person = :person and u.state = :state')
                ->setParameter('person', $this->getUser())
                ->setParameter('state', $form->get('state')->getData())
                ->orderBy('u.id', 'ASC');
            if ($rg->getId()) {
                $dql->andWhere('u != :rg')->setParameter('rg', $rg);
            }
            $has = $dql->getQuery()->getResult();
            if ($has) {
                $form->get('state')->addError(new FormError($this->get('translator')->trans('You already have an ID registered for this State')));

                return array('form' => $form->createView());
            }
            $manager->persist($rg);
            $manager->flush();
            $resp = new Response('<script>rgGrid.getGrid();</script>');

            return $resp;
        }

        return array('form' => $form->createView());
    }

    private function checkRGDce($rg)
    {
        $total = ($rg[1] * 2) + ($rg[2] * 3) + ($rg[3] * 4) + ($rg[4] * 5) + ($rg[5]
                * 6) + ($rg[6] * 7) + ($rg[7] * 8) + ($rg[8] * 9);
        $resto = $total % 11;

        if ($resto == 0 || $resto == 1) {
            return 1;
        } else {
            return 11 - $resto;
        }
    }

    private function checkRGDcd($rg)
    {
        $n1 = ($rg[8] * 2) % 9;
        $n2 = ($rg[6] * 2) % 9;
        $n3 = ($rg[4] * 2) % 9;
        $n4 = ($rg[2] * 2) % 9;
        $n5 = ($rg[0] * 2) % 9;
        $total = $n1 + $n2 + $n3 + $n4 + $n5 + $rg[7] + $rg[5] + $rg[3] + $rg[1];

        if ($rg[8] == 9) {
            $total = $total + 9;
        }
        if ($rg[6] == 9) {
            $total = $total + 9;
        }
        if ($rg[4] == 9) {
            $total = $total + 9;
        }
        if ($rg[2] == 9) {
            $total = $total + 9;
        }
        if ($rg[0] == 9) {
            $total = $total + 9;
        }

        $resto = $total % 10;

        if ($resto == 0) {
            return 1;
        } else {
            return 10 - $resto;
        }
    }

    /**
     * @Route("/profile/doc/rg/list", name="lc_profile_doc_rg_list")
     * @Template()
     */
    public function docRgListAction(Request $request)
    {
        $sql = $this->getDoctrine()->getManager()
            ->getRepository('LoginCidadaoCoreBundle:IdCard')
            ->getGridQuery($this->getUser());

        $grid = new GridHelper();
        $grid->setId('rg-grid');
        $grid->setPerPage(4);
        $grid->setMaxResult(4);
        $grid->setQueryBuilder($sql);
        $grid->setInfiniteGrid(true);
        $grid->setRoute('lc_profile_doc_rg_list');

        return array('grid' => $grid->createView($request));
    }

    /**
     * @Route("/register/prefilled", name="lc_prefilled_registration")
     */
    public function preFilledRegistrationAction(Request $request)
    {
        if (null !== $this->getUser()) {
            return $this->get('templating')->renderResponse('LoginCidadaoCoreBundle:Person:registration/errorAlreadyLoggedin.html.twig');
        }
        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->get('fos_user.registration.form.factory');
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');
        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        /** @var PersonInterface $user */
        $user = $userManager->createUser();
        $user->setEnabled(true);

        $fullName = $request->get('full_name');

        if (!is_null($fullName)) {
            $name = explode(' ', trim($fullName), 2);
            $user->setFirstName($name[0]);
            $user->setSurname($name[1]);
        }
        $user->setEmail($request->get('email'));
        $user->setMobile($request->get('mobile'));

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $formFactory->createForm();

        $form->add('firstName', 'text',
            array('required' => false, 'label' => 'form.firstName', 'translation_domain' => 'FOSUserBundle'))
            ->add('surname', 'text',
                array('required' => false, 'label' => 'form.surname', 'translation_domain' => 'FOSUserBundle'));

        $form->setData($user);

        if ('POST' === $request->getMethod()) {
            $form->bind($request);

            if ($form->isValid()) {
                $event = new FormEvent($form, $request);
                $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS,
                    $event);

                $userManager->updateUser($user);

                if (null === $response = $event->getResponse()) {
                    $url = $this->get('router')->generate('fos_user_registration_confirmed');
                    $response = new RedirectResponse($url);
                }

                $dispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED,
                    new FilterUserResponseEvent($user, $request, $response));

                return $response;
            }
        }

        return $this->get('templating')->renderResponse('LoginCidadaoCoreBundle:Person:registration/preFilledRegistration.html.twig',
            array(
                'form' => $form->createView(),
                'actionUrl' => 'lc_prefilled_registration',
            ));
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

        return array('allBadges' => $badges, 'userBadges' => $user->getBadges());
    }

    private function removeAll(array $objects)
    {
        $em = $this->getDoctrine()->getManager();
        foreach ($objects as $object) {
            $em->remove($object);
        }
    }

    private function trans($id, array $parameters = array(), $domain = null, $locale = null)
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
            ->findBy([
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
