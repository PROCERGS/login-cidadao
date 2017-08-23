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

use LoginCidadao\CoreBundle\Security\TwoFactorAuthenticationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use LoginCidadao\CoreBundle\Form\Type\TwoFactorAuthenticationFormType;
use LoginCidadao\CoreBundle\Form\Type\TwoFactorAuthenticationDisableFormType;
use LoginCidadao\CoreBundle\Form\Type\TwoFactorAuthenticationBackupCodeGenerationFormType;
use Symfony\Component\Form\FormError;

/**
 * @Route("/two-factor")
 */
class TwoFactorAuthenticationController extends Controller
{

    /**
     * @Route("/enable", name="2fa_enable")
     * @Template()
     */
    public function enableAction(Request $request)
    {
        /** @var TwoFactorAuthenticationService $twoFactor */
        $twoFactor = $this->get('lc.two_factor');

        $translator = $this->get('translator');
        $person = $this->getUser();
        $person->setGoogleAuthenticatorSecret($twoFactor->generateSecret());

        $form = $this->createForm(new TwoFactorAuthenticationFormType(), $person);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $verificationCode = $form->get('verification')->getData();

            try {
                $twoFactor->enable($form->getData(), $verificationCode);

                $message = $translator->trans('Two-Factor Authentication enabled.');
                $this->get('session')->getFlashBag()->add('success', $message);

                return $this->redirect($this->generateUrl("fos_user_change_password"));
            } catch (\InvalidArgumentException $e) {
                $message = $translator->trans($e->getMessage());
                $form->get('verification')->addError(new FormError($message));
            }
        }

        return ['form' => $form->createView(), 'secretUrl' => $twoFactor->getSecretUrl($person)];
    }

    /**
     * @Route("/disable", name="2fa_disable")
     * @Template()
     */
    public function disableAction(Request $request)
    {
        /** @var TwoFactorAuthenticationService $twoFactor */
        $twoFactor = $this->get('lc.two_factor');

        $person = $this->getUser();
        $form = $this->createForm(new TwoFactorAuthenticationDisableFormType(), $person);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $translator = $this->get('translator');
            $twoFactor->disable($person);
            $message = $translator->trans('Two-Factor Authentication disabled.');
            $this->get('session')->getFlashBag()->add('success', $message);

            return $this->redirect($this->generateUrl("fos_user_change_password"));
        }

        return ['form' => $form->createView()];
    }

    /**
     * @Route("/backup-codes/generate", name="2fa_backup_codes_generate")
     * @Template()
     */
    public function generateBackupCodesAction(Request $request)
    {
        /** @var TwoFactorAuthenticationService $twoFactor */
        $twoFactor = $this->get('lc.two_factor');

        $person = $this->getUser();
        $form = $this->createForm(new TwoFactorAuthenticationBackupCodeGenerationFormType(), $person);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $twoFactor->removeBackupCodes($person);
            $twoFactor->generateBackupCodes($person);

            $message = $this->get('translator')
                ->trans('New Backup Codes generated. Don\'t forget to copy and store them safely.');
            $this->get('session')->getFlashBag()->add('success', $message);

            return $this->redirect($this->generateUrl("fos_user_change_password"));
        }

        return ['form' => $form->createView()];
    }

    /**
     * @Route("/teste")
     * @Template()
     */
    public function formAction()
    {
        return [];
    }
}
