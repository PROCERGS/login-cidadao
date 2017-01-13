<?php

namespace PROCERGS\LoginCidadao\AccountingBundle\Controller;

use LoginCidadao\OAuthBundle\Entity\AccessTokenRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/accounting", name="lc_accounting_data")
     */
    public function indexAction()
    {
        /** @var AccessTokenRepository $repo */
        $repo = $this->getDoctrine()->getRepository('LoginCidadaoOAuthBundle:AccessToken');

        $start = new \DateTime('2017-01-01');
        $end = new \DateTime('2017-02-01');
        $data = $repo->getAccounting($start, $end);
        var_dump($data);
        die();
    }
}
