<?php

namespace PROCERGS\LoginCidadao\NfgBundle\Controller;

use PROCERGS\LoginCidadao\NfgBundle\Service\Nfg;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/connect", name="nfg_connect")
     */
    public function connectAction(Request $request)
    {
        /** @var Nfg $nfg */
        $nfg = $this->get('procergs.nfg.service');

        return $nfg->login();
    }

    /**
     * @Route("/callback", name="nfg_callback")
     */
    public function indexAction()
    {
        return $this->render('PROCERGSNfgBundle:Default:index.html.twig');
    }
}
