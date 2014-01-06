<?php

namespace PROCERGS\LoginCidadao\UIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="ui_home")
     * @Template()
     */
    public function indexAction()
    {
        $security = $this->get('security.context');
        if (false === $security->isGranted('ROLE_USER')) {
            return array();
        } else {
            $user = $security->getToken()->getUser();
            return $this->render(
                'PROCERGSLoginCidadaoUIBundle:Default:index.loggedIn.html.twig', 
                compact('user')
            );
        }
    }
}
