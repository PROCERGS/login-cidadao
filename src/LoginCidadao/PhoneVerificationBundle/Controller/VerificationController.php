<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\PhoneVerificationBundle\Controller;

use LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface;
use LoginCidadao\PhoneVerificationBundle\Service\PhoneVerificationService;
use LoginCidadao\TaskStackBundle\Service\TaskStackManager;
use LoginCidadao\TaskStackBundle\Service\TaskStackManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @codeCoverageIgnore
 */
class VerificationController extends Controller
{
    /**
     * @Route("/verify/{id}", name="lc_verify_phone")
     * @Template()
     */
    public function verifyAction(Request $request, $id)
    {
        /** @var PhoneVerificationService $phoneVerificationService */
        $phoneVerificationService = $this->get('phone_verification');

        /** @var PhoneVerificationInterface $pendingVerifications */
        $verification = $phoneVerificationService->getPendingPhoneVerificationById($this->getUser(), $id);

        $form = $this->createForm('LoginCidadao\PhoneVerificationBundle\Form\PhoneVerificationType');
        $form->handleRequest($request);
        $verified = false;

        if ($form->isValid()) {
            $code = $form->getData()['verificationCode'];
            $verified = $phoneVerificationService->verify($verification, $code);
            if (!$verified) {
                /** @var TranslatorInterface $translator */
                $translator = $this->get('translator');

                $error = new FormError($translator->trans('tasks.verify_phone.form.code.invalid_code'));
                $form->get('verificationCode')->addError($error);
            }
        }

        if (!$verification || $verified) {
            /** @var TaskStackManagerInterface $taskStackManager */
            $taskStackManager = $this->get('task_stack.manager');
            $task = $taskStackManager->getCurrentTask();
            $taskStackManager->setTaskSkipped($task);

            return $taskStackManager->processRequest($request, $this->redirectToRoute('lc_dashboard'));
        }

        return ['verification' => $verification, 'form' => $form->createView()];
    }

    /**
     * @Route("/resend/{id}", name="lc_resend_verification_code")
     * @Template()
     */
    public function resendAction(Request $request, $id)
    {
        /** @var PhoneVerificationService $phoneVerificationService */
        $phoneVerificationService = $this->get('phone_verification');

        $verification = $phoneVerificationService->getPendingPhoneVerificationById($this->getUser(), $id);

        try {
            $phoneVerificationService->resendVerificationCode($verification);

            // TODO: flash message sent
            return $this->redirectToRoute('lc_verify_phone', ['id' => $id]);
        } catch (TooManyRequestsHttpException $e) {
            // TODO: error, message not sent
            die('not sent');
        }

        return [];
    }
}
