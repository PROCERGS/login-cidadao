<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\PhoneVerificationBundle\Controller;

use LoginCidadao\PhoneVerificationBundle\Event\SendPhoneVerificationEvent;
use LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface;
use LoginCidadao\PhoneVerificationBundle\PhoneVerificationEvents;
use LoginCidadao\PhoneVerificationBundle\Service\PhoneVerificationService;
use PROCERGS\LoginCidadao\PhoneVerificationBundle\Service\VerificationSentService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/verify_resend", name="resend_verification_code")
     */
    public function indexAction()
    {
        /** @var PhoneVerificationService $phoneVerificationService */
        $phoneVerificationService = $this->get('phone_verification');

        $phoneVerification = $phoneVerificationService->getPendingPhoneVerification(
            $this->getUser(),
            $this->getUser()->getMobile()
        );

        /** @var EventDispatcherInterface $dispatcher */
        $dispatcher = $this->get('event_dispatcher');

        $sent = 0;
        $notSent = 0;
        if (!$this->canResend($phoneVerification)) {
            $notSent++;
        } else {
            $event = new SendPhoneVerificationEvent($phoneVerification);
            $dispatcher->dispatch(PhoneVerificationEvents::PHONE_VERIFICATION_REQUESTED, $event);
            $sent++;
        }

        die("sent: $sent | not sent: $notSent");
    }

    private function canResend(PhoneVerificationInterface $phoneVerification)
    {
        $limit = new \DateTime('-5 minutes');
        /** @var VerificationSentService $verificationSentService */
        $verificationSentService = $this->get('verification_sent');
        $last = $verificationSentService->getLastVerificationSent($phoneVerification);

        if (!$last) {
            return true;
        }

        return $last->getSentAt() < $limit;
    }
}
