<?php

namespace LoginCidadao\TaskStackBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('LoginCidadaoTaskStackBundle:Default:index.html.twig', array('name' => $name));
    }
}
