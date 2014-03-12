<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class ClientsController extends Controller
{

    /**
     * @Route("/apps_detail/{clientId}", name="lc_apps_detail")
     * @Template()
     */
    public function appsDetailAction($clientId)
    {
        $em = $this->getDoctrine()->getManager();
        $clients = $em->getRepository('PROCERGSOAuthBundle:Client');
        $client = $clients->find($clientId);

        return $this->render(
                        'PROCERGSLoginCidadaoCoreBundle:Person:appsDetail.html.twig',
                        compact('client')
        );
    }

    /**
     * @Route("/apps", name="lc_apps")
     * @Template()
     */
    public function appsAction()
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

        return $this->render(
                        'PROCERGSLoginCidadaoCoreBundle:Person:apps.html.twig',
                        compact('user', 'apps')
        );
    }

}
