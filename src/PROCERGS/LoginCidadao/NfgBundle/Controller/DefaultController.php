<?php

namespace PROCERGS\LoginCidadao\NfgBundle\Controller;

use FOS\UserBundle\Security\LoginManager;
use PROCERGS\LoginCidadao\NfgBundle\Exception\NfgServiceUnavailableException;
use PROCERGS\LoginCidadao\NfgBundle\Service\Nfg;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/connect", name="nfg_connect")
     */
    public function connectAction(Request $request)
    {
        $nfg = $this->getNfgService();

        try {
            $response = $nfg->login();
        } catch (NfgServiceUnavailableException $e) {
            $response = $this->redirectToRoute('nfg_unavailable');
        }

        return $response;
    }

    /**
     * @Route("/connect/wait", name="nfg_wait_connection")
     */
    public function waitConnectionAction(Request $request)
    {
        return $this->render('PROCERGSNfgBundle::connecting.html.twig');
    }

    /**
     * @Route("/unavailable", name="nfg_unavailable")
     */
    public function unavailableAction(Request $request)
    {
        return new Response('NFG unavailable');
    }

    /**
     * @Route("/callback", name="nfg_callback")
     */
    public function indexAction(Request $request)
    {
        $nfg = $this->getNfgService();

        /** @var LoginManager $loginManager */
        $loginManager = $this->get('fos_user.security.login_manager');
        $params = [
            'cpf' => $request->get('cpf'),
            'accessId' => $request->get('accessid'),
            'prsec' => $request->get('prsec'),
        ];
        $secret = $this->getParameter('procergs.nfg.authentication.hmac_secret');

        $response = $nfg->loginCallback($request->getSession(), $loginManager, $params, $secret);

        return new Response('SUCCESS');
    }

    /**
     * @return Nfg
     */
    private function getNfgService()
    {
        return $this->get('procergs.nfg.service');
    }
}
