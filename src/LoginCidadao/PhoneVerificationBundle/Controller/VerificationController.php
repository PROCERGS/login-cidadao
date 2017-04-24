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
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @codeCoverageIgnore
 */
class VerificationController extends Controller
{
    /**
     * @Route("/verify", name="lc_verify_phone")
     * @Template()
     */
    public function verifyAction(Request $request)
    {
        $form = $this->createForm('LoginCidadao\PhoneVerificationBundle\Form\PhoneVerificationType');
        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var PhoneVerificationService $phoneVerificationService */
            $phoneVerificationService = $this->get('phone_verification');

            /** @var PhoneVerificationInterface[] $pendingVerifications */
            $pendingVerifications = $phoneVerificationService->getAllPendingPhoneVerification($this->getUser());

            /** @var PhoneVerificationInterface $phoneVerification */
            $phoneVerification = $form->getData();
            $code = $form->getData()['verificationCode'];

            $success = false;
            foreach ($pendingVerifications as $verification) {
                if ($phoneVerificationService->verify($verification, $code)) {
                    $success = true;
                }
            }

            if (!$success) {
                /** @var TranslatorInterface $translator */
                $translator = $this->get('translator');

                $error = new FormError($translator->trans('tasks.verify_phone.form.code.invalid_code'));
                $form->get('verificationCode')->addError($error);
            }
        }

        return ['form' => $form->createView()];
    }
}
