<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PROCERGS\LoginCidadao\CoreBundle\Model\PersonInterface;
use PROCERGS\LoginCidadao\CoreBundle\Form\Type\TwoFactorAuthenticationFormType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Security\Core\Util\SecureRandom;
use PROCERGS\LoginCidadao\CoreBundle\Entity\BackupCode;
use Doctrine\ORM\EntityManager;

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
                $em = $this->getDoctrine()->getManager();
                $this->generateBackupCodes($em, $person);
                $person = $form->getData();
                $em->persist($person);
                $em->flush();
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
     * @Route("/disable")
     * @Template()
     */
    public function disableAction()
    {
        return array();
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
