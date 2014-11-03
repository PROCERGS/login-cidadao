<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use PROCERGS\LoginCidadao\CoreBundle\Form\Type\ContactFormType;
use PROCERGS\LoginCidadao\CoreBundle\Entity\SentEmail;
use PROCERGS\OAuthBundle\Entity\Client;

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
