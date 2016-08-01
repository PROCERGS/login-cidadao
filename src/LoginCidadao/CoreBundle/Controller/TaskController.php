<?php

namespace LoginCidadao\CoreBundle\Controller;

use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Util\TokenGenerator;
use FOS\UserBundle\Event\GetResponseUserEvent;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\CoreBundle\Service\IntentManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class TaskController extends Controller
{
    /**
     * @Route("/confirm-email", name="task_confirm_email")
     * @Template()
     */
    public function confirmEmailAction(Request $request)
    {
        /** @var IntentManager $intentManager */
        $intentManager = $this->get('lc.intent.manager');
        $person = $this->getUser();
        $resend = $request->get('resend', false);

        $hasIntent = $intentManager->hasIntent($request);
        $targetUrl = $hasIntent ? $intentManager->getIntent($request) : $this->generateUrl(
            'task_confirm_email',
            ['success' => '✓']
        );

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
        if ($form->isValid()) {
            $event = new FormEvent($person, $request);
            $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_SUCCESS, $event);

            // TODO: persist

            $response = $this->render(
                'LoginCidadaoCoreBundle:Task:confirmEmail.html.twig',
                ['targetUrl' => $targetUrl, 'form' => $form->createView()]
            );
            $event = new FilterUserResponseEvent($person, $request, $response);
            $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_COMPLETED, $event);
            $response = $event->getResponse();
        }

        if ($resend) {
            $this->resendEmailConfirmation($request->getSession(), $person);
        }

        if ($response instanceof Response) {
            return $response;
        }

        return ['targetUrl' => $targetUrl, 'form' => $form->createView()];
    }

    private function resendEmailConfirmation(SessionInterface $session, PersonInterface $person)
    {
        $mailer = $this->get('fos_user.mailer');
        $translator = $this->get('translator');

        if (is_null($person->getEmailConfirmedAt())) {
            if (is_null($person->getConfirmationToken())) {
                $tokenGenerator = new TokenGenerator();
                $person->setConfirmationToken($tokenGenerator->generateToken());
                $userManager = $this->get('fos_user.user_manager');
                $userManager->updateUser($person);
            }
            $mailer->sendConfirmationEmailMessage($person);

            if ($session instanceof Session) {
                $session->getFlashBag()->add(
                    'success',
                    $translator->trans("tasks.confirm_email.resent.alert")
                );
            }

            return $this->redirectToRoute('task_confirm_email');
        }
    }
}
