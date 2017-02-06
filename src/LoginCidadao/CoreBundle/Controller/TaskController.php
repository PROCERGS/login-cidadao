<?php

namespace LoginCidadao\CoreBundle\Controller;

use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Util\TokenGenerator;
use FOS\UserBundle\Event\GetResponseUserEvent;
use LoginCidadao\CoreBundle\Entity\Person;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\CoreBundle\Service\IntentManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

class TaskController extends Controller
{
    /**
     * @param Request $request
     * @return array|null|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     *
     * @Route("/confirm-email", name="task_confirm_email")
     * @Template()
     */
    public function confirmEmailAction(Request $request)
    {
        /** @var IntentManager $intentManager */
        $intentManager = $this->get('lc.intent.manager');
        /** @var PersonInterface $person */
        $person = $this->getUser();
        $resend = $request->get('resend', false);
        $notifySent = $request->get('resent', false);
        $originalEmail = $person->getEmail();

        $hasIntent = $intentManager->hasIntent($request);
        $targetUrl = $hasIntent ? $intentManager->getIntent($request) : $this->generateUrl('lc_dashboard');

        if ($person->getEmailConfirmedAt()) {
            if ($hasIntent) {
                return $this->redirect($intentManager->consumeIntent($request));
            } else {
                return $this->redirectToRoute('lc_dashboard');
            }
        }

        /** @var EventDispatcher $dispatcher */
        $dispatcher = $this->get('event_dispatcher');
        $event = new GetResponseUserEvent($person, $request);
        $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_INITIALIZE, $event);

        $form = $this->createForm('LoginCidadao\CoreBundle\Form\Type\EmailFormType', $person);

        $response = null;
        $form->handleRequest($request);
        $emailChanged = false;
        if ($form->isValid()) {
            $emailChanged = $originalEmail !== $person->getEmail();
            if ($emailChanged) {
                /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
                $userManager = $this->get('fos_user.user_manager');

                $event = new FormEvent($form, $request);
                $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_SUCCESS, $event);

                $userManager->updateUser($person);

                $response = $this->redirectToRoute('task_confirm_email', ['resent' => '✓']);
                $event = new FilterUserResponseEvent($person, $request, $response);
                $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_COMPLETED, $event);
                $response = $event->getResponse();
            } else {
                $resend = true;
            }
        }

        if ($emailChanged || $notifySent) {
            $this->flashEmailSent();
        }

        if ($resend) {
            $this->resendEmailConfirmation($person);
        }

        if ($response instanceof Response) {
            return $response;
        }

        return ['targetUrl' => $targetUrl, 'form' => $form->createView()];
    }

    /**
     * @param Request $request
     * @param $service
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Route("/register/fill-email/{service}", name="task_fill_email")
     * @Template()
     */
    public function fillEmailAction(Request $request, $service)
    {
        $session = $request->getSession();
        $userInfo = $session->get("$service.userinfo");

        $person = new Person();
        $person->setEmail($userInfo['email']);

        $form = $this->createForm('LoginCidadao\CoreBundle\Form\Type\EmailFormType', $person);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $session->set("$service.email", $form->getData()->getEmail());

            return $this->redirect(
                $this->generateUrl(
                    'hwi_oauth_service_redirect',
                    array('service' => $service)
                )
            );
        }

        return ['form' => $form->createView(), 'service' => $service];
    }

    private function resendEmailConfirmation(PersonInterface $person)
    {
        $mailer = $this->get('fos_user.mailer');

        if (is_null($person->getEmailConfirmedAt())) {
            if (is_null($person->getConfirmationToken())) {
                $tokenGenerator = new TokenGenerator();
                $person->setConfirmationToken($tokenGenerator->generateToken());
                $userManager = $this->get('fos_user.user_manager');
                $userManager->updateUser($person);
            }
            $mailer->sendConfirmationEmailMessage($person);

            $this->flashEmailSent();

            return $this->redirectToRoute('task_confirm_email');
        }
    }

    private function flashEmailSent()
    {
        /** @var TranslatorInterface $translator */
        $translator = $this->get('translator');
        $this->addFlash('success', $translator->trans("tasks.confirm_email.resent.alert"));
    }
}
