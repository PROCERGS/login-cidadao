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

use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserManagerInterface;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\CoreBundle\Security\User\Manager\UserManager;
use LoginCidadao\PhoneVerificationBundle\Exception\VerificationNotSentException;
use LoginCidadao\PhoneVerificationBundle\Form\PhoneNumberFormType;
use LoginCidadao\PhoneVerificationBundle\Form\PhoneVerificationType;
use LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface;
use LoginCidadao\PhoneVerificationBundle\Service\PhoneVerificationServiceInterface;
use LoginCidadao\TaskStackBundle\Service\TaskStackManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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
     * @Route("/task/verify-phone/{id}", name="lc_verify_phone")
     * @Template()
     */
    public function verifyAction(Request $request, $id)
    {
        /** @var PhoneVerificationServiceInterface $phoneVerificationService */
        $phoneVerificationService = $this->get('phone_verification');

        /** @var PhoneVerificationInterface $pendingVerifications */
        $verification = $phoneVerificationService->getPendingPhoneVerificationById($id, $this->getUser());

        if (!$verification) {
            return $this->noVerificationOrVerified($request);
        }

        $editPhoneForm = $this->createForm(PhoneNumberFormType::class, $verification->getPerson(), [
            'action' => $this->generateUrl('lc_phone_verification_edit_phone', ['verificationId' => $id]),
        ]);
        $form = $this->createForm(PhoneVerificationType::class);
        $form->handleRequest($request);
        $verified = false;

        if ($form->isValid()) {
            $code = $form->getData()['verificationCode'];
            $verified = $phoneVerificationService->verify($verification, $code);
            if (!$verified) {
                $form->get('verificationCode')->addError(new FormError(
                    $this->get('translator')->trans('tasks.verify_phone.form.errors.verificationCode.invalid_code')
                ));
            }
        }

        if (!$verification || $verified) {
            return $this->noVerificationOrVerified($request);
        }

        $nextResend = $this->getNextResendDate($verification);
        $mandatory = $phoneVerificationService->isVerificationMandatory($verification);

        return [
            'verification' => $verification,
            'nextResend' => $nextResend,
            'mandatory' => $mandatory,
            'form' => $form->createView(),
            'editPhoneForm' => $editPhoneForm->createView(),
        ];
    }

    /**
     * @Route("/task/verify-phone/{id}/success", name="lc_phone_verification_success")
     * @Template("LoginCidadaoPhoneVerificationBundle:Verification:response.html.twig")
     */
    public function successAction(Request $request, $id)
    {
        /** @var TranslatorInterface $translator */
        $translator = $this->get('translator');

        return [
            'message' => $translator->trans('tasks.verify_phone.success'),
            'target' => $this->generateUrl('lc_dashboard'),
        ];
    }

    /**
     * @Route("/task/verify-phone/{id}/resend", name="lc_phone_verification_code_resend")
     * @Template()
     */
    public function resendAction(Request $request, $id)
    {
        /** @var PhoneVerificationServiceInterface $phoneVerificationService */
        $phoneVerificationService = $this->get('phone_verification');

        $verification = $phoneVerificationService->getPendingPhoneVerificationById($id, $this->getUser());
        if (!$verification instanceof PhoneVerificationInterface) {
            throw $this->createNotFoundException();
        }

        try {
            $phoneVerificationService->sendVerificationCode($verification);

            $result = ['type' => 'success', 'message' => 'tasks.verify_phone.resend.success'];
        } catch (TooManyRequestsHttpException $e) {
            $result = ['type' => 'danger', 'message' => 'tasks.verify_phone.resend.errors.too_many_requests'];
        } catch (VerificationNotSentException $e) {
            $result = ['type' => 'danger', 'message' => 'tasks.verify_phone.resend.errors.unavailable'];
        }

        /** @var TranslatorInterface $translator */
        $translator = $this->get('translator');
        $this->addFlash($result['type'], $translator->trans($result['message']));

        return $this->redirectToRoute('lc_verify_phone', ['id' => $id]);
    }

    /**
     * @Route("/task/verify-phone/{verificationId}/edit-phone", name="lc_phone_verification_edit_phone")
     */
    public function editPhoneAction(Request $request, $verificationId)
    {
        /** @var PersonInterface $person */
        $person = $this->getUser();
        $response = $this->redirectToRoute('lc_verify_phone', ['id' => $verificationId]);

        /** @var EventDispatcherInterface $dispatcher */
        $dispatcher = $this->get('event_dispatcher');
        $event = new GetResponseUserEvent($person, $request);
        $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_INITIALIZE, $event);

        $editPhoneForm = $this->createForm(PhoneNumberFormType::class, $person, [
            'action' => $this->generateUrl('lc_phone_verification_edit_phone', ['verificationId' => $verificationId]),
        ]);
        $editPhoneForm->handleRequest($request);
        if ($editPhoneForm->isValid()) {
            /** @var $userManager UserManager */
            $userManager = $this->get('lc.user_manager');

            $event = new FormEvent($editPhoneForm, $request);
            $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_SUCCESS, $event);

            $userManager->updateUser($person);

            $event = new FilterUserResponseEvent($person, $request, $response);
            $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_COMPLETED, $event);
            $response = $event->getResponse();
        }

        return $response;
    }

    /**
     * @Route("/task/verify-phone/{id}/skip", name="lc_phone_verification_skip")
     * @Template()
     */
    public function skipAction(Request $request, $id)
    {
        /** @var TaskStackManagerInterface $taskStackManager */
        $taskStackManager = $this->get('task_stack.manager');

        $task = $taskStackManager->getCurrentTask();
        if ($task) {
            $taskStackManager->setTaskSkipped($task);
        }

        return $taskStackManager->processRequest($request, $this->redirectToRoute('lc_dashboard'));
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

        $verification = $phoneVerificationService->getPhoneVerificationById($id);

        if (!$verification || false === $phoneVerificationService->verifyToken($verification, $token)) {
            /** @var TranslatorInterface $translator */
            $translator = $this->get('translator');

            return $this->render(
                'LoginCidadaoPhoneVerificationBundle:Verification:response.html.twig',
                [
                    'message' => $translator->trans('tasks.verify_phone.failure'),
                    'target' => $this->generateUrl('lc_dashboard'),
                ]
            );
        }

        return $this->redirectToRoute('lc_phone_verification_success', ['id' => $verification->getId()]);
    }

    private function noVerificationOrVerified(Request $request)
    {
        /** @var TaskStackManagerInterface $taskStackManager */
        $taskStackManager = $this->get('task_stack.manager');
        $task = $taskStackManager->getCurrentTask();
        if ($task) {
            $taskStackManager->setTaskSkipped($task);
        }

        return $taskStackManager->processRequest($request, $this->redirectToRoute('lc_dashboard'));
    }

    private function getNextResendDate(PhoneVerificationInterface $verification)
    {
        /** @var PhoneVerificationServiceInterface $phoneVerificationService */
        $phoneVerificationService = $this->get('phone_verification');

        $nextResend = $phoneVerificationService->getNextResendDate($verification);
        if ($nextResend <= new \DateTime()) {
            $nextResend = false;
        }

        return $nextResend;
    }
}
