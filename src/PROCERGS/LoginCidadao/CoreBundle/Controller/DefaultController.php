<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="lc_home")
     * @Template()
     */
    public function indexAction()
    {
        $security = $this->get('security.context');
        if (false === $security->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
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
                'PROCERGSLoginCidadaoCoreBundle:Default:index.loggedIn.html.twig',
                compact('user', 'apps')
            );
        }
    }
}
