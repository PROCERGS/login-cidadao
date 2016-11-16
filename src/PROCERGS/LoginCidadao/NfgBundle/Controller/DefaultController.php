<?php

namespace PROCERGS\LoginCidadao\NfgBundle\Controller;

use Ejsmont\CircuitBreaker\CircuitBreakerInterface;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use PROCERGS\LoginCidadao\CoreBundle\Entity\PersonMeuRS;
use PROCERGS\LoginCidadao\CoreBundle\Helper\MeuRSHelper;
use PROCERGS\LoginCidadao\NfgBundle\Service\Nfg;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
            $personMeuRS = $meuRSHelper->getPersonMeuRS($this->getUser());
        } else {
            $personMeuRS = new PersonMeuRS();
        }

        $nfg = $this->getNfgService();

        return $nfg->connectCallback($request, $personMeuRS);
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
     * @return Nfg
     */
    private function getNfgService()
    {
        return $this->get('procergs.nfg.service');
    }

    private function isNfgServiceAvailable()
    {
        $serviceName = $this->getParameter('procergs.nfg.circuit_breaker.service_name');
        if (false === $this->has('circuitBreaker') || !$serviceName) {
            // We don't have Circuit Breaker enabled, so we assume the service is available
            return true;
        }

        /** @var CircuitBreakerInterface $cb */
        $cb = $this->get('circuitBreaker');

        return $cb->isAvailable($serviceName);
    }
}
