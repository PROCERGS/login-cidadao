<?php

namespace LoginCidadao\APIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('LoginCidadaoAPIBundle:Default:index.html.twig', array('name' => $name));
    }
}
