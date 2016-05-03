<?php
/*
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use LoginCidadao\CoreBundle\Entity\InvalidateSessionRequest;

/**
 * @Route("/invalidate")
 */
class InvalidateSessionsController extends Controller
{

    /**
     * @Route("/invalidate", name="lc_invalidate_sessions")
     * @Template()
     */
    public function invalidateAction(Request $request)
    {
        $invalidate = new InvalidateSessionRequest();

        $type = 'LoginCidadao\CoreBundle\Form\InvalidateSessionRequestType';
        $form = $this->createForm($type, $invalidate);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $invalidate->setPerson($this->getUser())
                ->setRequestedAt(new \DateTime());

            $em = $this->getDoctrine()->getManager();
            $em->persist($invalidate);
            $em->flush($invalidate);

            return $this->redirectToRoute('fos_user_security_logout');
        }

        return compact('form');
    }
}
