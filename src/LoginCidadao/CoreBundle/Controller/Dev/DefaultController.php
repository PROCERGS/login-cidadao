<?php

namespace LoginCidadao\CoreBundle\Controller\Dev;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/dev")
 */
class DefaultController extends Controller
{

    /**
     * @Route("/", name="lc_dev")
     * @Template()
     */
    public function indexAction()
    {
        return $this->redirect($this->generateUrl('lc_dev_client'));
    }

}
