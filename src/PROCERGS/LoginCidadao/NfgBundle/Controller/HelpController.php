<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\NfgBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class HelpController
 * @package PROCERGS\LoginCidadao\NfgBundle\Controller
 * @codeCoverageIgnore
 *
 * @Route("/help")
 */
class HelpController extends Controller
{
    /**
     * @Route("/connection-not-found", name="nfg_help_connection_not_found")
     */
    public function connectionNotFoundAction()
    {
        return $this->render('PROCERGSNfgBundle:Help:connectionNotFound.html.twig');
    }

    /**
     * @Route("/already-connected", name="nfg_help_already_connected")
     */
    public function alreadyConnectedAction(Request $request)
    {
        return $this->render(
            'PROCERGSNfgBundle:Help:alreadyConnected.html.twig',
            ['access_token' => $request->get('access_token')]
        );
    }

    /**
     * @Route("/cpf-didnt-match", name="nfg_help_cpf_did_not_match")
     */
    public function cpfDidNotMatchAction(Request $request)
    {
        return $this->render(
            'PROCERGSNfgBundle:Help:cpfDidNotMatch.html.twig',
            ['access_token' => $request->get('access_token')]
        );
    }

    /**
     * @Route("/email-in-use", name="nfg_help_email_in_use")
     */
    public function emailInUseAction(Request $request)
    {
        return $this->render('PROCERGSNfgBundle:Help:emailInUse.html.twig');
    }
}
