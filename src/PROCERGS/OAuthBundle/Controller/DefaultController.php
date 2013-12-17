<?php

namespace PROCERGS\OAuthBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('PROCERGSOAuthBundle:Default:index.html.twig', array('name' => $name));
    }
}
