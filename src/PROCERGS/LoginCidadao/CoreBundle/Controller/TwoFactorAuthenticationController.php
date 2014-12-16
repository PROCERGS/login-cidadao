<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PROCERGS\LoginCidadao\CoreBundle\Model\PersonInterface;
use PROCERGS\LoginCidadao\CoreBundle\Form\Type\TwoFactorAuthenticationFormType;
use PROCERGS\LoginCidadao\CoreBundle\Form\Type\TwoFactorAuthenticationDisableFormType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Security\Core\Util\SecureRandom;
use PROCERGS\LoginCidadao\CoreBundle\Entity\BackupCode;
use Doctrine\ORM\EntityManager;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorWithBackupInterface;

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
        $twoFactor = $this->get("scheb_two_factor.security.google_authenticator");
        $translator = $this->get('translator');
        $person = $this->getPerson();
        $secret = $twoFactor->generateSecret();
        $person->setGoogleAuthenticatorSecret($secret);

        $form = $this->createForm(new TwoFactorAuthenticationFormType(), $person);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $verificationCode = $form->get('verification')->getData();
            $isValid = $twoFactor->checkCode($person, $verificationCode);

            if ($isValid) {
                $this->enable2FA($person, $form);
                $message = $translator->trans('Two-Factor Authentication enabled.');
                $this->get('session')->getFlashBag()->add('success', $message);
                return $this->redirect($this->generateUrl("fos_user_change_password"));
            } else {
                $message = $translator->trans('Invalid code! Make sure you configured your app correctly and your smartphone\'s time is adjusted.');
                $form->get('verification')->addError(new FormError($message));
            }
        }

        return array(
            'form' => $form->createView(),
            'secretUrl' => $twoFactor->getUrl($person)
        );
    }

    /**
     * @Route("/disable", name="2fa_disable")
     * @Template()
     */
    public function disableAction(Request $request)
    {
        $person = $this->getPerson();
        $form = $this->createForm(new TwoFactorAuthenticationDisableFormType(), $person);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $translator = $this->get('translator');
            $this->disable2FA($person, $form);
            $message = $translator->trans('Two-Factor Authentication disabled.');
            $this->get('session')->getFlashBag()->add('success', $message);
            return $this->redirect($this->generateUrl("fos_user_change_password"));
        }

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/teste")
     * @Template()
     */
    public function formAction()
    {
        return array();
    }

    /**
     * @return PersonInterface
     */
    protected function getPerson()
    {
        return $this->getUser();
    }

    protected function enable2FA(PersonInterface $person, $form)
    {
        $em = $this->getDoctrine()->getManager();
        $this->generateBackupCodes($em, $person);
        $em->persist($form->getData());
        $em->flush();
    }

    protected function disable2FA(TwoFactorWithBackupInterface $person, $form)
    {
        $em = $this->getDoctrine()->getManager();
        $backupCodes = $person->getBackupCodes();
        foreach ($backupCodes as $backupCode) {
            $em->remove($backupCode);
        }
        $person->setGoogleAuthenticatorSecret(null);
        $em->persist($person);
        $em->flush();
    }

    protected function generateBackupCodes(EntityManager $em,
                                           PersonInterface $person)
    {
        $generator = new SecureRandom();
        $backupCodes = array();
        while (count($backupCodes) < 10) {
            $code = bin2hex($generator->nextBytes(5));
            $backupCode = new BackupCode();
            $backupCode->setPerson($person);
            $backupCode->setCode($code);
            $backupCodes[] = $backupCode;
            $em->persist($backupCode);
        }
    }

}
