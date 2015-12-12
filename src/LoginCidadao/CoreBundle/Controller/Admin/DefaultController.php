<?php

namespace LoginCidadao\CoreBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/admin")
 */
class DefaultController extends Controller
{

    /**
     * @Route("/", name="lc_admin")
     * @Template()
     */
    public function indexAction()
    {
        return $this->redirect($this->generateUrl('lc_admin_app'));
    }

}
