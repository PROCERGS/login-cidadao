<?php

namespace LoginCidadao\LocaleBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class LocaleController extends Controller
{
    /**
     * @Route("/set/{_locale}")
     */
    public function setAction(Request $request, $_locale)
    {
        return $this->redirect($this->generateUrl('lc_home'));
    }

}
