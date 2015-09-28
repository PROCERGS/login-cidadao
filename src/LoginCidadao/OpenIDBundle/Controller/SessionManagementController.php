<?php
/*
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/openid/connect")
 */
class SessionManagementController extends Controller
{

    /**
     * @Route("/session/check")
     * @Method({"GET"})
     * @Template
     */
    public function checkSessionAction(Request $request)
    {
        return array();
    }

    /**
     * @Route("/session/end")
     */
    public function endSessionAction(Request $request)
    {
        //
    }
}
