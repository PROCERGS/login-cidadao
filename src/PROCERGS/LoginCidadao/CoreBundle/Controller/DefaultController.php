<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Exception\DisabledException;
use PROCERGS\LoginCidadao\CoreBundle\Form\Type\LoginFormType;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Person;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Login;

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
            return $this->redirect($this->generateUrl('fos_user_registration_register'));
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

    /**
     * @Route("/lc_home_gateway", name="lc_home_gateway")
     * @Template()
     */
    public function gatewayAction(Request $request)
    {
        return array('home1' => $this->generateUrl('lc_home', array(), true));
    }

    /**
     * @Route("/apps", name="lc_apps")
     * @Template()
     */
    public function appsAction(Request $request)
    {
        $security = $this->get('security.context');
        if (false === $security->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('fos_user_registration_register'));
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
                'PROCERGSLoginCidadaoCoreBundle:Person:apps.html.twig',
                compact('user', 'apps')
            );
        }
    }

    /**
     * @Route("/general", name="lc_general")
     * @Template()
     */
    public function generalAction(Request $request)
    {
        return $this->render(
            'PROCERGSLoginCidadaoCoreBundle:Info:terms.html.twig',
            compact('user', 'apps')
        );
    }
}