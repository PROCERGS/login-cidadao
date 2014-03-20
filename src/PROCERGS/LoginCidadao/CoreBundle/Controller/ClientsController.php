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
        $clientScopes = $client->getAllowedScopes();

        $user = $this->getUser();

        $clientScopes = $client->getAllowedScopes();

        $authorization = $this->getDoctrine()
                ->getRepository('PROCERGSLoginCidadaoCoreBundle:Authorization')
                ->findOneBy(array(
            'person' => $user,
            'client' => $client
        ));
        $userScopes = empty($authorization) ? array() : $authorization->getScope();

        $scopes = array();
        foreach ($clientScopes as $s) {
            $scopes[$s] = in_array($s, $userScopes) ? true : false;
        }

        $csrf_token = $this->get('form.csrf_provider')->generateCsrfToken('authenticate');

        return $this->render(
                        'PROCERGSLoginCidadaoCoreBundle:Person:appsDetail.html.twig',
                        compact('user', 'client', 'scopes', 'csrf_token')
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
