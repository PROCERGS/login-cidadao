<?php

namespace LoginCidadao\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use LoginCidadao\CoreBundle\Entity\Person;

class ExternalController extends Controller
{

    /**
     * @Route("/external/navbar.js", name="lc_navbar_external")
     * @Template()
     */
    public function navbarAction(Request $request)
    {
        $clientId = $request->get('app_id');
        $hasAppId = strlen($clientId) > 0;

        $user           = $this->getUser();
        $userAuthorized = $hasAppId && $user instanceof Person && $user->isClientAuthorized($clientId);

        $external = true;
        $navbar   = $this->renderView('LoginCidadaoCoreBundle:External:navbar.html.twig',
            compact('external'));
        $html     = json_encode(array('navbar' => $navbar));
        $response = $this->render('LoginCidadaoCoreBundle:External:navbar.js.twig',
            compact('html', 'userAuthorized'));
        $response->headers->set('Content-Type', 'application/javascript');
        return $response;
    }
}
