<?php

namespace LoginCidadao\InfiniteScrollBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use LoginCidadao\InfiniteScrollBundle\Model\NotificationIterable;
use LoginCidadao\NotificationBundle\Handler\NotificationHandlerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class FragmentController extends Controller
{

    public function fragmentAction($name)
    {
        return $this->render('LoginCidadaoInfiniteScrollBundle:Default:index.html.twig',
                                array('name' => $name));
    }

}
