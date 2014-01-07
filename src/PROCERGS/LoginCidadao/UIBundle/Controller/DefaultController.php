<?php

namespace PROCERGS\LoginCidadao\UIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PROCERGS\OAuthBundle\Entity\Client;

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
            $em = $this->getDoctrine()->getManager();
            $clients = $em->getRepository('PROCERGSOAuthBundle:Client');
            
            $user = $security->getToken()->getUser();
            $allApps = $clients->findAll();
            
            $apps = array();
            // Filtering off authorized apps
            foreach ($allApps as $app) {
                if ($user->hasAuthorization($app)) {
                    continue;
                }
                $apps[] = $app;
            }
            
            return $this->render(
                'PROCERGSLoginCidadaoUIBundle:Default:index.loggedIn.html.twig', 
                compact('user', 'apps')
            );
        }
    }
}
