<?php

namespace PROCERGS\LoginCidadao\NotificationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class BroadcastController extends Controller
{

    /**
     * @Route("/")
     * @Template()
     */
    public function listAction()
    {
        return array();
    }

    /**
     * @Route("/new")
     * @Template()
     */
    public function newAction()
    {
        return array();
    }

}
