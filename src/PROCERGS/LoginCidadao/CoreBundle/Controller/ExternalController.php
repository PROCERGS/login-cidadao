<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Person;

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
        $navbar   = $this->renderView('PROCERGSLoginCidadaoCoreBundle:External:navbar.html.twig',
            compact('external'));
        $html     = json_encode(array('navbar' => $navbar));
        $response = $this->render('PROCERGSLoginCidadaoCoreBundle:External:navbar.js.twig',
            compact('html', 'userAuthorized'));
        $response->headers->set('Content-Type', 'application/javascript');
        return $response;
    }
}
