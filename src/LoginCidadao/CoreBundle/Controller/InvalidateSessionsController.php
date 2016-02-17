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

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use LoginCidadao\CoreBundle\Entity\InvalidateSessionRequest;

/**
 * @Route("/invalidate")
 */
class InvalidateSessionsController extends Controller
{

    /**
     * @Route("/debug")
     * @Template()
     */
    public function debugAction()
    {
        $metadata = $this->getSession()->getMetadataBag();
        $now      = time();

        return compact('metadata', 'now');
    }

    /**
     * @Route("/invalidate")
     * @Template()
     */
    public function invalidateAction()
    {
        $request = new InvalidateSessionRequest();
        $request->setPerson($this->getUser())
            ->setRequestedAt(new \DateTime());

        $em = $this->getDoctrine()->getManager();

        $em->persist($request);
        $em->flush($request);

        return array();
    }

    /**
     * @return SessionInterface
     */
    private function getSession()
    {
        return $this->get('session');
    }
}
