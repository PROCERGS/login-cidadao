<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Controller\Dev;

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
