<?php

namespace LoginCidadao\CoreBundle\Controller;

use FOS\UserBundle\Util\TokenGenerator;
use LoginCidadao\CoreBundle\Service\IntentManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

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

        if ($resend) {
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

                $request->getSession()->getFlashBag()->add(
                    'success',
                    $translator->trans("tasks.confirm_email.resent.alert")
                );

                return $this->redirectToRoute('task_confirm_email');
            }
        }

        return ['targetUrl' => $targetUrl];
    }
}
