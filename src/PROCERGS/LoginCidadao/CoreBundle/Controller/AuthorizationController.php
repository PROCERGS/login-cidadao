<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class AuthorizationController extends Controller
{

    /**
     * @Route("/authorize/{clientId}", name="lc_authorize")
     * @Template()
     */
    public function newAction($clientId)
    {
        $repo = $this->getDoctrine()->getRepository('PROCERGSOAuthBundle:Client');
        $client = $repo->find($clientId);

        $form = $this->container->get('fos_oauth_server.authorize.form')->createView();
        $formHandler = $this->container->get('fos_oauth_server.authorize.form.handler');

        return compact('client', 'form', 'formHandler');
    }

}
