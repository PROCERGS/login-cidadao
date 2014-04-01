<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class AuthorizationController extends Controller
{

    /**
     * @Route("/authorizations", name="lc_apps")
     * @Template()
     */
    public function userAuthorizationsAction()
    {
        $em = $this->getDoctrine()->getManager();
        $clients = $em->getRepository('PROCERGSOAuthBundle:Client');

        $user = $this->getUser();
        $allApps = $clients->findAll();

        $apps = array();
        // Filtering off authorized apps
        foreach ($allApps as $app) {
            if ($user->hasAuthorization($app)) {
                continue;
            }
            $apps[] = $app;
        }

        return compact('user', 'apps');
    }

}
