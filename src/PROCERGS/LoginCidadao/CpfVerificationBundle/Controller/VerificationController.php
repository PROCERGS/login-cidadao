<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\CpfVerificationBundle\Controller;

use LoginCidadao\CoreBundle\Model\PersonInterface;
use PROCERGS\LoginCidadao\CpfVerificationBundle\Exception\CpfVerificationException;
use PROCERGS\LoginCidadao\CpfVerificationBundle\Model\SelectMotherInitialsChallenge;
use PROCERGS\LoginCidadao\CpfVerificationBundle\Service\CpfVerificationService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

/**
 * Class VerificationController
 * @package PROCERGS\LoginCidadao\CpfVerificationBundle\Controller
 * @codeCoverageIgnore
 */
class VerificationController extends Controller
{
    /**
     * @Route("/cpf-verification", name="cpf_verification_verify")
     * @return Response
     */
    public function verifyAction()
    {
        /** @var PersonInterface $person */
        $person = $this->getUser();

        /** @var CpfVerificationService $service */
        $service = $this->get('procergs.nfg.cpf_verification.service');
        try {
            dump($challenge = $service->selectChallenge(new SelectMotherInitialsChallenge(2, $person->getCpf(), [])));
            dump($service->listAvailableChallenges($person->getCpf()));
            dump($service->answerChallenge($challenge, 'X'));
        } catch (CpfVerificationException|TooManyRequestsHttpException $e) {
            dump($e);
        }

        return new Response('ok');
    }
}
