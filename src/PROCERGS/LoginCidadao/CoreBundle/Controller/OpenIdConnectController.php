<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class OpenIdConnectController extends Controller
{

    /**
     * @Route("/authorize", name="_authorize_handle")
     * @Method({"POST"})
     */
    public function handleAuthorizeAction()
    {
        $server = $this->get('oauth2.server');

        return $server->handleAuthorizeRequest($this->get('oauth2.request'),
                $this->get('oauth2.response'), true, $this->getUser()->getId());
    }
}
