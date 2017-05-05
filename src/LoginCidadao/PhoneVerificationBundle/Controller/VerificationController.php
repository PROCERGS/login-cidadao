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

use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\PhoneVerificationBundle\Exception\VerificationNotSentException;
use LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface;
use LoginCidadao\PhoneVerificationBundle\Service\PhoneVerificationServiceInterface;
use LoginCidadao\TaskStackBundle\Service\TaskStackManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @codeCoverageIgnore
 */
class VerificationController extends Controller
{
    private function getVerificationOr404($id)
    {
        /** @var PhoneVerificationServiceInterface $phoneVerificationService */
        $phoneVerificationService = $this->get('phone_verification');

        $verification = $phoneVerificationService->getPhoneVerificationById($id);

        if (!$verification) {
            throw new NotFoundHttpException();
        }

        return $verification;
    }

    /**
     * @Route("/verify/{id}", name="lc_verify_phone")
     * @Template()
     */
    public function verifyAction(Request $request, $id)
    {
        /** @var PhoneVerificationServiceInterface $phoneVerificationService */
        $phoneVerificationService = $this->get('phone_verification');

        /** @var PhoneVerificationInterface $pendingVerifications */
        $verification = $phoneVerificationService->getPendingPhoneVerificationById($id, $this->getUser());

        $form = $this->createForm('LoginCidadao\PhoneVerificationBundle\Form\PhoneVerificationType');
        $form->handleRequest($request);
        $verified = false;

        if ($form->isValid()) {
            $code = $form->getData()['verificationCode'];
            $verified = $phoneVerificationService->verify($verification, $code);
            if (!$verified) {
                /** @var TranslatorInterface $translator */
                $translator = $this->get('translator');

                $error = new FormError(
                    $translator->trans('tasks.verify_phone.form.errors.verificationCode.invalid_code')
                );
                $form->get('verificationCode')->addError($error);
            }
        }

        if (!$verification || $verified) {
            /** @var TaskStackManagerInterface $taskStackManager */
            $taskStackManager = $this->get('task_stack.manager');
            $task = $taskStackManager->getCurrentTask();
            $taskStackManager->setTaskSkipped($task);

            return $taskStackManager->processRequest($request, $this->redirectToRoute('lc_phone_verification_success'));
        }

        $nextResend = $phoneVerificationService->getNextResendDate($verification);
        if ($nextResend <= new \DateTime()) {
            $nextResend = false;
        }

        return ['verification' => $verification, 'nextResend' => $nextResend, 'form' => $form->createView()];
    }

    /**
     * @Route("/phone-verification/success", name="lc_phone_verification_success")
     * @Template()
     */
    public function successAction(Request $request)
    {
        return [
            'target' => '#',
        ];
    }

    /**
     * @Route("/resend/{id}", name="lc_resend_verification_code")
     * @Template()
     */
    public function resendAction(Request $request, $id)
    {
        /** @var PhoneVerificationServiceInterface $phoneVerificationService */
        $phoneVerificationService = $this->get('phone_verification');

        $verification = $phoneVerificationService->getPendingPhoneVerificationById($id, $this->getUser());

        try {
            $phoneVerificationService->resendVerificationCode($verification);

            // TODO: flash "message sent"
        } catch (TooManyRequestsHttpException $e) {
            // TODO: flash "message not sent. you have to wait until Y-m-d H:i"
        } catch (VerificationNotSentException $e) {
            // TODO: error, message not sent
            die('error. not sent');
        }

        return $this->redirectToRoute('lc_verify_phone', ['id' => $id]);
    }

    /**
     * This route is used to verify the phone using a Verification Token.
     *
     * The objective is that the user can simply click a link received via SMS and the phone would be verified
     *
     * @Route("/v/{id}/{token}", name="lc_phone_verification_verify_link")
     * @Template()
     */
    public function clickToVerifyAction(Request $request, $id, $token)
    {
        /** @var PhoneVerificationServiceInterface $phoneVerificationService */
        $phoneVerificationService = $this->get('phone_verification');

        $verification = $this->getVerificationOr404($id);

        $redirectSuccess = $this->redirectToRoute('lc_phone_verification_success');
        if (!$verification) {
            throw new NotFoundHttpException();
        }

        try {
            if ($phoneVerificationService->verifyToken($verification, $token)) {
                return $redirectSuccess;
            } else {
                return new JsonResponse(false);
            }
        } catch (NotFoundHttpException $e) {
            if ($this->getUser() instanceof PersonInterface) {
                return $this->redirectToRoute('lc_verify_phone', ['id' => $id]);
            } else {
                throw $e;
            }
        }
    }
}
