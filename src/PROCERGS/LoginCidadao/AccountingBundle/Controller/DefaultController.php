<?php

namespace PROCERGS\LoginCidadao\AccountingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('PROCERGSLoginCidadaoAccountingBundle:Default:index.html.twig', array('name' => $name));
    }
}
