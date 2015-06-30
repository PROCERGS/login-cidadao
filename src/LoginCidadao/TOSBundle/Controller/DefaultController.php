<?php

namespace LoginCidadao\TOSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{

    public function indexAction($name)
    {
        return $this->render('LoginCidadaoTOSBundle:Default:index.html.twig',
                array('name' => $name));
    }
}
