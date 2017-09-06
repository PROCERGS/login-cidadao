<?php

namespace PROCERGS\LoginCidadao\NfgBundle\Controller;

use Eljam\CircuitBreaker\Breaker;
use Eljam\CircuitBreaker\Exception\CircuitOpenException;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use PROCERGS\LoginCidadao\CoreBundle\Entity\PersonMeuRS;
use PROCERGS\LoginCidadao\CoreBundle\Helper\MeuRSHelper;
use PROCERGS\LoginCidadao\NfgBundle\Service\Nfg;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @codeCoverageIgnore
 */
class DefaultController extends Controller
{
    /**
     * @Route("/select-action", name="nfg_action_chooser")
     */
    public function actionChooserAction()
    {
        return $this->render('PROCERGSNfgBundle:Default:actionChooser.html.twig');
    }

    /**
     * @Route("/connect", name="nfg_connect")
     */
    public function connectAction(Request $request)
    {
        $nfg = $this->getNfgService();

        $response = $nfg->connect();

        return $response;
    }

    /**
     * @Route("/connect/callback", name="nfg_connect_callback")
     */
    public function connectCallbackAction(Request $request)
    {
        if ($this->getUser() instanceof PersonInterface) {
            /** @var MeuRSHelper $meuRSHelper */
            $meuRSHelper = $this->get('meurs.helper');
            $personMeuRS = $meuRSHelper->getPersonMeuRS($this->getUser(), true);
        } else {
            $personMeuRS = new PersonMeuRS();
        }

        $nfg = $this->getNfgService();
        $override = $request->get('override', false) ? true : false;

        return $nfg->connectCallback($request, $personMeuRS, $override);
    }

    /**
     * @Route("/login", name="nfg_login")
     */
    public function loginAction(Request $request)
    {
        $nfg = $this->getNfgService();

        $response = $nfg->login();

        return $response;
    }

    /**
     * @Route("/wait/{action}", name="nfg_wait", requirements={"action": "(connect|login)"})
     */
    public function waitConnectionAction(Request $request, $action)
    {
        if (false === $this->isNfgServiceAvailable()) {
            return $this->redirectToRoute('nfg_unavailable');
        }

        return $this->render('PROCERGSNfgBundle::connecting.html.twig', compact('action'));
    }

    /**
     * @Route("/unavailable", name="nfg_unavailable")
     */
    public function unavailableAction(Request $request)
    {
        return $this->render('PROCERGSNfgBundle:Default:unavailable.html.twig');
    }

    /**
     * @Route("/missing-info", name="nfg_missing_info")
     */
    public function missingInfoAction(Request $request)
    {
        return $this->render('PROCERGSNfgBundle:Default:missing-info.html.twig');
    }

    /**
     * @Route("/login/callback", name="nfg_login_callback")
     */
    public function loginCallbackAction(Request $request)
    {
        $nfg = $this->getNfgService();

        $params = [
            'cpf' => $request->get('cpf'),
            'accessId' => $request->get('accessid'),
            'prsec' => $request->get('prsec'),
        ];
        $secret = $this->getParameter('procergs.nfg.authentication.hmac_secret');

        $response = $nfg->loginCallback($params, $secret);

        return $response;
    }

    /**
     * @Route("/disconnect", name="nfg_disconnect")
     */
    public function disconnectAction(Request $request)
    {
        /** @var MeuRSHelper $meuRSHelper */
        $meuRSHelper = $this->get('meurs.helper');
        $personMeuRS = $meuRSHelper->getPersonMeuRS($this->getUser());

        $nfg = $this->getNfgService();

        return $nfg->disconnect($personMeuRS);
    }

    /**
     * @return object|Nfg
     */
    private function getNfgService()
    {
        return $this->get('procergs.nfg.service');
    }

    private function isNfgServiceAvailable()
    {
        if (false === $this->has('procergs.nfg.circuit_breaker')) {
            // We don't have Circuit Breaker enabled, so we assume the service is available
            return true;
        }

        /** @var Breaker $cb */
        $cb = $this->get('procergs.nfg.circuit_breaker');
        try {
            return $cb->protect(function () {
                return true;
            });
        } catch (CircuitOpenException $e) {
            return false;
        }
    }
}
