<?php

namespace LoginCidadao\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use LoginCidadao\CoreBundle\Entity\ClientSuggestion;

class AuthorizationController extends Controller
{

    /**
     * @Route("/authorizations", name="lc_authorization_list")
     * @Template()
     */
    public function listAction(Request $request)
    {
        $em      = $this->getDoctrine()->getManager();
        $clients = $em->getRepository('LoginCidadaoOAuthBundle:Client');

        $user    = $this->getUser();
        $allApps = $clients->findAll();

        $apps = array();
        // Filtering off authorized apps
        foreach ($allApps as $app) {
            if ($user->hasAuthorization($app)) {
                continue;
            }
            if ($app->isVisible()) {
                $apps[] = $app;
            }
        }

        $sugg        = new ClientSuggestion();
        $formBuilder = $this->createFormBuilder($sugg);
        $formBuilder->add('text',
            'Symfony\Component\Form\Extension\Core\Type\TextareaType');
        $form        = $formBuilder->getForm();

        $suggs = $em->getRepository('LoginCidadaoCoreBundle:ClientSuggestion')->findBy(array(
            'person' => $user), array('createdAt' => 'desc'), 6);
        $form  = $form->createView();

        $defaultClientUid = $this->container->getParameter('oauth_default_client.uid');

        return compact('user', 'apps', 'form', 'suggs', 'defaultClientUid');
    }
}
