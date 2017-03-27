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
use Symfony\Component\HttpFoundation\Request;

class VerificationController extends Controller
{
    /**
     * @Route("/verify/{code}")
     * @Template()
     */
    public function verifyAction(Request $request, $code)
    {
        /** @var PhoneVerificationService $phoneVerificationService */
        $phoneVerificationService = $this->get('phone_verification');

        /** @var PhoneVerificationInterface[] $pendingVerifications */
        $pendingVerifications = $phoneVerificationService->getAllPendingPhoneVerification($this->getUser());

        $em = $this->getDoctrine()->getManager();
        $success = false;
        foreach ($pendingVerifications as $verification) {
            if ($phoneVerificationService->checkVerificationCode($code, $verification->getVerificationCode())) {
                $verification->setVerifiedAt(new \DateTime());
                $em->persist($verification);
                $em->flush($verification);
                $success = true;
                break;
            }
        }

        return compact('success');
    }

}
